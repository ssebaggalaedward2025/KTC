<?php

use App\Exceptions\AppendOnlyViolation;
use App\Models\AuditLog;
use App\Models\User;
use App\Support\Audit;
use Illuminate\Support\Facades\DB;

it('records model changes with old and new values', function () {
    $user = User::factory()->create(['name' => 'Old Name']);

    $user->update(['name' => 'New Name']);

    $log = AuditLog::where('subject_id', $user->id)->where('event', 'updated')->sole();
    expect($log->attribute_changes['old']['name'])->toBe('Old Name')
        ->and($log->attribute_changes['attributes']['name'])->toBe('New Name');
});

it('never writes secrets to the audit log', function () {
    $user = User::factory()->create();
    $user->update(['password' => 'Another-Secret-1!', 'name' => 'Changed']);

    AuditLog::all()->each(function (AuditLog $log) {
        expect(json_encode($log->attribute_changes))->not->toContain('password');
    });
});

it('stores IP address, user agent and a hash on each row', function () {
    $this->withServerVariables(['REMOTE_ADDR' => '10.1.2.3'])
        ->withHeader('User-Agent', 'PestBrowser/1.0')
        ->post('/login', ['email' => 'nobody@example.test', 'password' => 'x']);

    $log = AuditLog::where('event', 'login_failed')->sole();

    expect($log->ip_address)->toBe('10.1.2.3')
        ->and($log->user_agent)->toBe('PestBrowser/1.0')
        ->and($log->row_hash)->toHaveLength(64);
});

it('refuses to update an audit row', function () {
    Audit::record('probe');

    AuditLog::first()->update(['description' => 'tampered']);
})->throws(AppendOnlyViolation::class);

it('refuses to delete an audit row', function () {
    Audit::record('probe');

    AuditLog::first()->delete();
})->throws(AppendOnlyViolation::class);

it('chains each row to the previous one', function () {
    Audit::record('one');
    Audit::record('two');
    Audit::record('three');

    $rows = AuditLog::orderBy('id')->get();
    expect($rows[0]->prev_hash)->toBeNull()
        ->and($rows[1]->prev_hash)->toBe($rows[0]->row_hash)
        ->and($rows[2]->prev_hash)->toBe($rows[1]->row_hash)
        ->and(AuditLog::verifyChain())->toBeNull();
});

it('detects tampering done directly in the database', function () {
    Audit::record('one');
    Audit::record('two');
    Audit::record('three');
    $second = AuditLog::orderBy('id')->skip(1)->first();

    // Simulates someone bypassing the application (e.g. with SQL access).
    DB::table('audit_logs')->where('id', $second->id)->update(['description' => 'tampered']);

    expect(AuditLog::verifyChain())->toBe($second->id);
    $this->artisan('bptms:audit-verify')->assertExitCode(1);
});

it('detects a deleted row', function () {
    Audit::record('one');
    Audit::record('two');
    Audit::record('three');
    $second = AuditLog::orderBy('id')->skip(1)->first();

    DB::table('audit_logs')->where('id', $second->id)->delete();

    expect(AuditLog::verifyChain())->not->toBeNull();
});

it('reports an intact chain', function () {
    Audit::record('one');

    $this->artisan('bptms:audit-verify')->assertExitCode(0);
});
