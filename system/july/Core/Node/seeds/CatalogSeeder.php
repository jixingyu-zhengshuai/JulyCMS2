<?php

namespace July\Core\Node\seeds;

use App\Database\SeederBase;
use Illuminate\Support\Facades\Date;

class CatalogSeeder extends SeederBase
{
    /**
     * 待填充的数据库表
     *
     * @var array
     */
    protected $tables = ['catalogs'];

    /**
     * 获取 catalogs 表数据
     *
     * @return array
     */
    protected function getCatalogsRecords()
    {
        $records = [
            [
                'id' => 'main',
                'is_necessary' => true,
                'label' => '默认目录',
            ],
        ];

        return array_map(function($record) {
            return $record + [
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
            ];
        }, $records);
    }
}
