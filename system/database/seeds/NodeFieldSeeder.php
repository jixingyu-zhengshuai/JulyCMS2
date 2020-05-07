<?php

use Illuminate\Database\Seeder;
use App\Models\NodeField;

class NodeFieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $fields = [
            [
                'truename' => 'title',
                'field_type' => 'text',
                'is_preset' => true,
                'config' => [
                    'langcode' => [
                        'interface_value' => 'zh',
                        'content_value' => 'en',
                    ],
                    'length' => 100,
                    'required' => true,
                    'index_weight' => 10,
                    'label' => [
                        'zh' => '标题',
                    ],
                    'help' => [
                        'zh' => '内容标题，通常用作链接文字',
                    ],
                    'description' => [
                        'zh' => '预设字段，不可删除',
                    ],
                ],
            ],
            [
                'truename' => 'url',
                'field_type' => 'text',
                'is_preset' => true,
                'is_searchable' => false,
                'config' => [
                    'langcode' => [
                        'interface_value' => 'zh',
                        'content_value' => 'en',
                    ],
                    'length' => 100,
                    'pattern' => 'url',
                    'label' => [
                        'zh' => '网址',
                    ],
                    'placeholder' => [
                        'en' => '/index.html',
                    ],
                ],
            ],
            [
                'truename' => 'template',
                'field_type' => 'text',
                'is_preset' => true,
                'is_searchable' => false,
                'config' => [
                    'langcode' => [
                        'interface_value' => 'zh',
                        'content_value' => 'en',
                    ],
                    'length' => 100,
                    'pattern' => 'twig',
                    'label' => [
                        'zh' => '模板',
                    ],
                    'help' => [
                        'zh' => 'twig 模板，用于生成页面',
                    ],
                    'placeholder' => [
                        'en' => 'template-name.twig',
                    ],
                ],
            ],
            [
                'truename' => 'meta_title',
                'field_type' => 'text',
                'is_preset' => true,
                'is_searchable' => false,
                'config' => [
                    'langcode' => [
                        'interface_value' => 'zh',
                        'content_value' => 'en',
                    ],
                    'length' => 100,
                    'label' => [
                        'zh' => '标题',
                    ],
                ],
            ],
            [
                'truename' => 'meta_description',
                'field_type' => 'text',
                'is_preset' => true,
                'is_searchable' => false,
                'config' => [
                    'langcode' => [
                        'interface_value' => 'zh',
                        'content_value' => 'en',
                    ],
                    'length' => 160,
                    'label' => [
                        'zh' => '描述',
                    ],
                ],
            ],
            [
                'truename' => 'meta_keywords',
                'field_type' => 'text',
                'is_preset' => true,
                'is_searchable' => false,
                'config' => [
                    'langcode' => [
                        'interface_value' => 'zh',
                        'content_value' => 'en',
                    ],
                    'length' => 160,
                    'label' => [
                        'zh' => '关键字',
                    ],
                ],
            ],
            [
                'truename' => 'content',
                'field_type' => 'html',
                'config' => [
                    'langcode' => [
                        'interface_value' => 'zh',
                        'content_value' => 'en',
                    ],
                    'index_weight' => 1,
                    'label' => [
                        'zh' => '内容',
                    ],
                ],
            ],
            [
                'truename' => 'h1',
                'field_type' => 'text',
                'config' => [
                    'langcode' => [
                        'interface_value' => 'zh',
                        'content_value' => 'en',
                    ],
                    'length' => 100,
                    'index_weight' => 10,
                    'label' => [
                        'zh' => 'H1',
                    ],
                ],
            ],
            [
                'truename' => 'image_src',
                'field_type' => 'file',
                'is_searchable' => false,
                'config' => [
                    'langcode' => [
                        'interface_value' => 'zh',
                        'content_value' => 'en',
                    ],
                    'file_type' => 'image',
                    'label' => [
                        'zh' => '主图',
                    ],
                ],
            ],
            [
                'truename' => 'image_alt',
                'field_type' => 'text',
                'is_searchable' => false,
                'config' => [
                    'langcode' => [
                        'interface_value' => 'zh',
                        'content_value' => 'en',
                    ],
                    'length' => 100,
                    'label' => [
                        'zh' => '主图 Alt',
                    ],
                ],
            ],
        ];

        foreach ($fields as $field) {
            NodeField::create($field);
        }
    }
}
