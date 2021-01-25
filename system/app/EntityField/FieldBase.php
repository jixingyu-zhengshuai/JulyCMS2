<?php

namespace App\EntityField;

use App\EntityField\FieldTypes\FieldTypeManager;
use App\Entity\EntityBase;
use App\Entity\EntityManager;
use App\Entity\Exceptions\InvalidEntityException;
use App\Models\ModelBase;
use App\Services\Translation\TranslatableInterface;
use App\Services\Translation\TranslatableTrait;
use App\Utils\Arr;
use App\Utils\Types;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

abstract class FieldBase extends ModelBase implements TranslatableInterface
{
    use TranslatableTrait;

    /**
     * 主键
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 主键类型
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * 模型主键是否递增
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * 可批量赋值的属性。
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'field_type_id',
        'is_reserved',
        'is_global',
        'group_title',
        'search_weight',
        'maxlength',
        'label',
        'description',
        'is_required',
        'helpertext',
        'default_value',
        'options',
        'langcode',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_reserved' => 'boolean',
        'is_global' => 'boolean',
        'search_weight' => 'int',
        'maxlength' => 'int',
        'is_required' => 'boolean',
    ];

    /**
     * 绑定的实体名
     *
     * @var string|null
     */
    protected $boundEntityName = null;

    /**
     * 字段所属实体
     *
     * @var \App\Entity\EntityBase
     */
    protected $entity;

    /**
     * 获取字段绑定实体的实体名
     *
     * @return string|null
     */
    public function getBoundEntityName()
    {
        if ($this->boundEntityName) {
            return $this->boundEntityName;
        } elseif ($this->entity) {
            return $this->entity->getEntityName();
        }
        return null;
    }

    /**
     * 获取绑定的实体
     *
     * @return \App\Entity\EntityBase|null
     */
    public function getBoundEntity()
    {
        return $this->entity;
    }

    /**
     * 绑定到实体
     *
     * @param  \App\Entity\EntityBase $entity
     * @return $this
     *
     * @throws \App\Entity\Exceptions\InvalidEntityException
     */
    public function bindEntity(EntityBase $entity)
    {
        $class = $this->boundEntityName ? EntityManager::resolve($this->boundEntityName) : null;
        if (!$class || $entity instanceof $class) {
            $this->entity = $entity;
            return $this;
        } else {
            throw new InvalidEntityException('当前字段无法绑定到实体：'.get_class($entity));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getLangcode()
    {
        if ($this->entity) {
            return $this->entity->getLangcode();
        }
        return $this->contentLangcode ?? $this->getOriginalLangcode();
    }

    /**
     * label 属性的 Get Mutator
     *
     * @param  string|null $label
     * @return string
     */
    public function getLabelAttribute($label)
    {
        if ($this->pivot) {
            $label = $this->pivot->label;
        }
        return trim($label);
    }

    /**
     * description 属性的 Get Mutator
     *
     * @param  string|null $description
     * @return string
     */
    public function getDescriptionAttribute($description)
    {
        if ($this->pivot) {
            $description = $this->pivot->description;
        }
        return trim($description);
    }

    /**
     * is_required 属性的 Get Mutator
     *
     * @param  bool|int $required
     * @return bool
     */
    public function getIsRequiredAttribute($required)
    {
        if ($this->pivot) {
            $required = $this->pivot->is_required;
        }
        return (bool) $required;
    }

    /**
     * helpertext 属性的 Get Mutator
     *
     * @param  string|null $helpertext
     * @return string
     */
    public function getHelpertextAttribute($helpertext)
    {
        if ($this->pivot) {
            $helpertext = $this->pivot->helpertext;
        }
        return trim($helpertext);
    }

    /**
     * default_value 属性的 Get Mutator
     *
     * @param  string|null $defaultValue
     * @return string
     */
    public function getDefaultValueAttribute($defaultValue)
    {
        if ($this->pivot) {
            $defaultValue = $this->pivot->defaultValue;
        }
        return Types::cast($defaultValue, $this->getFieldType()->getCaster());
    }

    /**
     * options 属性的 Get Mutator
     *
     * @param  string|null $options
     * @return array
     */
    public function getOptionsAttribute($options)
    {
        if ($this->pivot) {
            $options = $this->pivot->options;
        }

        if (empty($options)) {
            return [];
        }

        $caster = $this->getFieldType()->getCaster();
        $options = array_map(function($option) use($caster) {
            return Types::cast($option, $caster);
        }, array_filter(array_map('trim', explode('|', $options))));

        return array_values($options);
    }

    /**
     * 获取字段参数
     *
     * @return array
     */
    public function getParameters()
    {
        // 尝试从缓存获取数据
        if ($result = $this->cachePipe(__FUNCTION__)) {
            return $result->value();
        }

        $parameters = null;

        // 获取翻译过的模型字段参数
        if ($this->entity && $this->entity->getMold()->isTranslated()) {
            $parameters = FieldParameters::ofField($this)->where('mold_id', $this->entity->mold_id)->first();
        }

        // 获取翻译过的字段参数
        elseif (!$this->entity && $this->isTranslated()) {
            $parameters = FieldParameters::ofField($this)->where('mold_id', null)->first();
        }

        if ($parameters) {
            return [
                'default_value' => $parameters->default_value,
                'options' => $parameters->options,
            ];
        }

        return [];
    }

    /**
     * 获取所有列和字段值
     *
     * @param  array $keys 限定键名
     * @return array
     */
    public function gather(array $keys = ['*'])
    {
        // 尝试从缓存获取数据
        if ($attributes = $this->cachePipe(__FUNCTION__)) {
            $attributes = $attributes->value();
        }

        // 生成属性数组
        else {
            $attributes = array_merge(
                $this->attributesToArray(), $this->getParameters()
            );
            $attributes['delta'] = $this->pivot ? intval($this->pivot->delta) : 0;
        }
        if ($keys && $keys !== ['*']) {
            $attributes = Arr::selectAs($attributes, $keys);
        }
        return $attributes;
    }

    /**
     * 获取字段类型对象
     *
     * @return \App\EntityField\FieldTypes\FieldTypeBase
     */
    public function getFieldType()
    {
        // 尝试从缓存获取数据
        if ($result = $this->cachePipe(__FUNCTION__)) {
            return $result->value();
        }
        return FieldTypeManager::findOrFail($this->attributes['field_type_id'])->bindField($this);
    }

    /**
     * 获取字段值模型
     *
     * @return \App\EntityField\FieldValueBase
     */
    public function getValueModel()
    {
        // 尝试从缓存获取数据
        if ($result = $this->cachePipe(__FUNCTION__)) {
            return $result->value();
        }
        return $this->getFieldType()->getValueModel();
    }

    /**
     * 获取存储字段值的动态数据库表的表名
     *
     * @return string
     */
    public function useDynamicValueTable()
    {
        return $this->getValueModel()->isDynamic();
    }

    /**
     * 获取存储字段值的动态数据库表的表名
     *
     * @return string
     */
    public function getDynamicValueTable()
    {
        return $this->getBoundEntityName().'__'.$this->getKey();
    }

    /**
     * 获取存储字段值的数据库表的表名
     *
     * @return string
     */
    public function getValueTable()
    {
        return $this->getValueModel()->getTable();
    }

    /**
     * 获取数据表列参数
     *
     * @return array
     */
    public function getValueColumn()
    {
        return $this->getFieldType()->getColumn();
    }

    /**
     * 获取字段值
     *
     * @return mixed
     */
    public function getValue()
    {
        if ($value = $this->cachePipe(__FUNCTION__)) {
            return $value->value();
        }
        return $this->getValueModel()->getValue($this->entity);
    }

    /**
     * 设置字段值
     *
     * @param  mixed $value
     * @return void
     */
    public function setValue($value)
    {
        return $this->getValueModel()->setValue($value, $this->entity);
    }

    /**
     * 删除字段值
     *
     * @return void
     */
    public function deleteValue()
    {
        return $this->getValueModel()->deleteValue($this->entity);
    }

    /**
     * 搜索字段值
     *
     * @param  string $needle 搜索该字符串
     * @return array
     */
    public function searchValue(string $needle)
    {
        return $this->getValueModel()->searchValue($needle);
    }

    /**
     * 建立字段值存储表
     *
     * @return void
     */
    public function tableUp()
    {
        // 检查是否使用动态表保存字段值，如果不是则不创建
        if (! $this->useDynamicValueTable()) {
            return;
        }

        // 获取独立表表名，并判断是否已存在
        $tableName = $this->getDynamicValueTable();
        if (Schema::hasTable($tableName)) {
            return;
        }

        // 获取用于创建数据表列的参数
        $column = $this->getValueColumn();

        // 创建数据表
        Schema::create($tableName, function (Blueprint $table) use ($column) {
            $table->id();
            $table->unsignedBigInteger('entity_id');

            $table->addColumn($column['type'], $column['name'], $column['parameters'] ?? []);

            $table->string('langcode', 12);
            $table->timestamps();

            $table->unique(['entity_id', 'langcode']);
        });
    }

    /**
     * 删除字段值存储表
     *
     * @return void
     */
    public function tableDown()
    {
        // 检查是否使用动态表保存字段值，如果不是则不创建
        if (! $this->useDynamicValueTable()) {
            return;
        }
        Schema::dropIfExists($this->getDynamicValueTable());
    }

    /**
     * {@inheritdoc}
     */
    public static function boot()
    {
        parent::boot();

        static::created(function(FieldBase $field) {
            $field->tableUp();
        });

        static::deleted(function(FieldBase $field) {
            $field->tableDown();
        });
    }

    /**
     * @return array[]
     */
    public function getValueRecords()
    {
        return DB::table($this->getValueTable())->get()->map(function ($record) {
            return (array) $record;
        })->all();
    }
}
