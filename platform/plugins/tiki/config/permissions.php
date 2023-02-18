<?php

return [
    [
        'name' => 'Tiki',
        'flag' => 'plugins.tiki',
    ],
    [
        'name'        => 'Discountcodes',
        'flag'        => 'discountcodes.index',
        'parent_flag' => 'plugins.tiki',
    ],
    [
        'name'        => 'Create',
        'flag'        => 'discountcodes.create',
        'parent_flag' => 'discountcodes.index',
    ],
    [
        'name'        => 'Edit',
        'flag'        => 'discountcodes.edit',
        'parent_flag' => 'discountcodes.index',
    ],
    [
        'name'        => 'Delete',
        'flag'        => 'discountcodes.destroy',
        'parent_flag' => 'discountcodes.index',
    ],
    [
        'name'        => 'Sellers',
        'flag'        => 'sellers.index',
        'parent_flag' => 'plugins.tiki',
    ],
    [
        'name'        => 'Create',
        'flag'        => 'sellers.create',
        'parent_flag' => 'sellers.index',
    ],
    [
        'name'        => 'Edit',
        'flag'        => 'sellers.edit',
        'parent_flag' => 'sellers.index',
    ],
    [
        'name'        => 'Delete',
        'flag'        => 'sellers.destroy',
        'parent_flag' => 'sellers.index',
    ],
];
