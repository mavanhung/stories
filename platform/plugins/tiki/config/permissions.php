<?php

return [
    [
        'name' => 'Tikis',
        'flag' => 'tiki.index',
    ],
    [
        'name'        => 'Create',
        'flag'        => 'tiki.create',
        'parent_flag' => 'tiki.index',
    ],
    [
        'name'        => 'Edit',
        'flag'        => 'tiki.edit',
        'parent_flag' => 'tiki.index',
    ],
    [
        'name'        => 'Delete',
        'flag'        => 'tiki.destroy',
        'parent_flag' => 'tiki.index',
    ],
];
