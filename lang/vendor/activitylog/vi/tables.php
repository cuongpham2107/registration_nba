<?php

return [
    'columns' => [
        'log_name' => [
            'label' => 'Loại',
        ],
        'event' => [
            'label' => 'Sự kiện',
        ],
        'subject_type' => [
            'label'        => 'Đối tượng',
            'soft_deleted' => ' (Đã xóa mềm)',
            'deleted'      => ' (Đã xóa)',
        ],
        'causer' => [
            'label' => 'Người thực hiện',
        ],
        'properties' => [
            'label' => 'Thuộc tính',
        ],
        'created_at' => [
            'label' => 'Thời điểm ghi nhật ký',
        ],
    ],
    'filters' => [
        'created_at' => [
            'label'                   => 'Thời điểm ghi nhật ký',
            'created_from'            => 'Tạo từ ',
            'created_from_indicator'  => 'Tạo từ  : :created_from',
            'created_until'           => 'Tạo đến ',
            'created_until_indicator' => 'Tạo đến  : :created_until',
        ],
        'event' => [
            'label' => 'Sự kiện',
        ],
        'log_name' => [
            'label' => 'Tên nhật ký',
        ],
    ],
];
