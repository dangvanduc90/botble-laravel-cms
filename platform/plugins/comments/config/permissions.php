<?php

return [
    [
        'name' => 'Comments',
        'flag' => 'comments.index',
    ],
    [
        'name'        => 'Create',
        'flag'        => 'comments.create',
        'parent_flag' => 'comments.index',
    ],
    [
        'name'        => 'Edit',
        'flag'        => 'comments.edit',
        'parent_flag' => 'comments.index',
    ],
    [
        'name'        => 'Delete',
        'flag'        => 'comments.destroy',
        'parent_flag' => 'comments.index',
    ],
];
