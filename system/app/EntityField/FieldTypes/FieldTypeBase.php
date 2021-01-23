<?php

namespace App\EntityField\FieldTypes;

use App\Concerns\HasAttributesTrait;
use App\Utils\Types;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\EntityField\FieldBase;
use App\EntityField\FieldValue;

/**
 * 模型字段类型定义类，简称定义类
 * 定义类主要用途：
 *  1. 辅助创建字段
 *  2. 构建字段数据表列
 *  3. 构建字段表单控件
 */
abstract class FieldTypeBase
{
    /**
     * 字段类型 id
     *
     * @var string
     */
    protected $id;

    /**
     * 字段类型标签
     *
     * @var string
     */
    protected $label;

    /**
     * 字段类型描述
     *
     * @var string|null
     */
    protected $description;

    /**
     * 字段值类型转换器
     *
     * @var string
     */
    protected $caster = 'string';

    /**
     * 绑定的字段对象
     *
     * @var \App\EntityField\FieldBase|null
     */
    protected $field = null;

    // /**
    //  * 字段参数读取语言
    //  *
    //  * @var string|null
    //  */
    // protected $langcode = null;

    // /**
    //  * 指示字段是否已翻译
    //  *
    //  * @var bool
    //  */
    // protected $translated = false;

    /**
     * 静态方式获取属性
     *
     * @param  string $key
     * @param  mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        return (new static)->$key ?? $default;
    }

    /**
     * @param \App\EntityField\FieldBase|null $field
     */
    public function __construct(FieldBase $field = null)
    {
        $this->field = $field;

        // 生成 id
        if (! $this->id) {
            $this->id = preg_replace('/_type$/', '', Str::snake(class_basename(static::class)));
        }

        // 生成标签
        if (! $this->label) {
            $this->label = preg_replace('/Type$/', '', class_basename(static::class));
        }
    }

    /**
     * 绑定字段对象
     *
     * @param  \App\EntityField\FieldBase $field
     * @return self
     */
    public function bindField(FieldBase $field)
    {
        $this->field = $field;

        return $this;
    }

    public function getDefaultValue()
    {
        return null;
    }

    /**
     * 获取字段值模型，用于管理字段值的增删改查等
     *
     * @return \App\EntityField\FieldValueBase
     */
    public function getValueModel()
    {
        $model = new FieldValue();
        if ($this->field) {
            $model->bindField($this->field);
        }
        return $model;
    }

    /**
     * 从表单数据中提取字段参数
     *
     * @param array $raw 包含表单数据的数组
     * @return array
     */
    public function extractParameters(array $raw)
    {
        $raw = $raw['parameters'] ?? $raw;
        $parameters = [];

        // 默认值
        if (isset($raw['default'])) {
            $parameters['default'] = Types::cast($raw['default'], $this->caster);
        }

        // 可选项
        $options = $raw['options'] ?? null;
        if ($options && is_array($options)) {
            $parameters['options'] = array_map(function($option) {
                return Types::cast($option, $this->caster);
            }, $options);
        }

        // 占位提示
        if (isset($raw['placeholder'])) {
            $parameters['placeholder'] = trim($raw['placeholder']);
        }

        return $parameters;
    }

    /**
     * 获取存储表表名
     *
     * @return string|null
     */
    public function getTable()
    {
        return null;
    }

    /**
     * 字段数据存储表的列信息，结构：
     * [
     *     type => string,
     *     name => string,
     *     parameters => array,
     * ]
     *
     * @return array
     */
    public function getColumn()
    {
        return [];
    }

    /**
     * 获取用于构建「字段生成/编辑表单」的材料，包括 HTML 片段，前端验证规则等
     *
     * @return array
     */
    public function getMaterials()
    {
        return [
            'id' => $this->field->getKey(),
            'field_type_id' => $this->id,
            'value' => $this->field->getDefaultValue(),
            'element' => $this->render(),
        ];
    }

    /**
     * 获取表单组件（element-ui component）
     *
     * @param  array|null $data 字段数据
     * @return string
     */
    public function render()
    {
        $data = $this->field->gather();
        $data['helpertext'] = $data['helpertext'] ?: $data['description'];
        $data['rules'] = $this->getRules();

        return view('field_types.'.$this->id, $data)->render();
    }

    /**
     * 获取验证规则（用于前端 js 验证）
     *
     * @return array
     */
    public function getRules()
    {
        $rules = [];
        $parameters = $this->field->getParameters();
        if ($parameters['required'] ?? false) {
            $rules[] = "{required:true, message:'不能为空', trigger:'submit'}";
        }
        return $rules;
    }

    /**
     * 获取验证器（用于后端验证）
     *
     * @param  array|null $parameters 字段参数
     * @return array
     */
    public function getValidator()
    {
        return [];
    }

    /**
     * 将记录转换为值
     *
     * @param  array $record 表记录
     * @return mixed
     */
    public function toValue(array $record)
    {
        $value = [];
        foreach ($this->getColumn() as $column) {
            $key = $column['name'];
            $value[$key] = isset($record[$key]) ? trim($record[$key]) : null;
        }

        return count($value) > 1 ? $value : reset($value);
    }

    /**
     * 将值转换为记录
     *
     * @param  mixed $value 字段值
     * @return array|null
     */
    public function toRecord($value)
    {
        $columns = $this->getColumn();
        return [
            $columns[0]['name'] => $value,
        ];
    }

    public function __get($name)
    {
        return $this->$name ?? null;
    }
}
