<?php

namespace July\Node\Seeds;

use Database\Seeds\SeederBase;
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
    protected function getCatalogsTableRecords()
    {
        $records = [
            [
                'id' => 'main',
                'is_reserved' => true,
                'label' => '默认目录，不可删除',
            ],
        ];

        $share = [
            'created_at' => Date::now(),
            'updated_at' => Date::now(),
        ];

        return array_map(function($record) use($share) {
            return $record + $share;
        }, $records);
    }
}