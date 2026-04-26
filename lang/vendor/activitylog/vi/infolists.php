<?php

return [
    'components' => [
        'created_by_at'             => '<strong>:subject</strong> đã được <strong>:event</strong> bởi <strong>:causer</strong>. <br><small> Cập nhật lúc: <strong>:update_at</strong></small>',
        'updater_updated'           => ':causer đã :event các thông tin sau: <br>:changes',
        'from_oldvalue_to_newvalue' => '- :key từ <strong>:old_value</strong> sang <strong>:new_value</strong>',
        'to_newvalue'               => '- :key <strong>:new_value</strong>',
        'unknown'                   => 'Không xác định',
    ],
];
