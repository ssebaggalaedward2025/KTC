<?php

use App\Rules\UgandaPhone;
use Illuminate\Support\Facades\Validator;

it('normalises common Ugandan formats to +256', function (string $input, string $expected) {
    expect(UgandaPhone::normalise($input))->toBe($expected);
})->with([
    ['0772123456', '+256772123456'],
    ['256772123456', '+256772123456'],
    ['+256 772 123 456', '+256772123456'],
    ['+256-772-123456', '+256772123456'],
]);

it('accepts valid numbers and rejects invalid ones', function (string $value, bool $valid) {
    $v = Validator::make(['p' => $value], ['p' => [new UgandaPhone]]);
    expect($v->passes())->toBe($valid);
})->with([
    ['+256772123456', true],
    ['+256414123456', true],
    ['+2567721234567', false],
    ['+256072123456', false],
    ['0772123456', false], // must be normalised first
    ['+254712345678', false],
]);
