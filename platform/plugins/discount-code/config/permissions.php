<?php

return [
    [
        'name' => 'Discount codes',
        'flag' => 'discount-code.index',
    ],
    [
        'name'        => 'Create',
        'flag'        => 'discount-code.create',
        'parent_flag' => 'discount-code.index',
    ],
    [
        'name'        => 'Edit',
        'flag'        => 'discount-code.edit',
        'parent_flag' => 'discount-code.index',
    ],
    [
        'name'        => 'Delete',
        'flag'        => 'discount-code.destroy',
        'parent_flag' => 'discount-code.index',
    ],
];
