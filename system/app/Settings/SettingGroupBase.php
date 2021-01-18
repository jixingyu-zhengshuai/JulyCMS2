<?php

namespace App\Settings;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\View;

abstract class SettingGroupBase
{
    /**
     * 配置组名称
     *
     * @var string
     */
    protected $name = '';

    /**
     * 配置组标题
     *
     * @var string
     */
    protected $title = '';

    /**
     * 配置项
     *
     * @var array
     */
    protected $items = [];

    /**
     * 配置组菜单项图标
     *
     * @var string
     */
    protected $icon = null;

    /**
     * 获取配置文件路径
     *
     * @return string
     */
    public function getPath()
    {
        return base_path('settings/'.$this->name.'.php');
    }

    /**
     * 加载配置
     *
     * @return void
     */
    public function load()
    {
        if (is_file($file = $this->getPath())) {
            $this->merge(require $file);
        }
    }

    /**
     * 整合配置数据到当前配置中，然后保存到文件
     *
     * @param  array $settings
     * @return void
     */
    public function save(array $settings)
    {
        // 过滤并整合配置数据到当前配置环境
        $this->merge($settings);

        // 保存配置数据到文件
        $content = '<?php'.PHP_EOL.PHP_EOL.'return '.trim(var_export($settings, TRUE)).';'.PHP_EOL;
        file_put_contents($this->getPath(), $content);
    }

    /**
     * @return \Illuminate\View\View
     */
    public function view()
    {
        $data = [
            'name' => $this->name,
            'title' => $this->title,
            'items' => $this->items,
            'settings' => [],
        ];

        foreach ($data['items'] as $key => $item) {
            $data['items'][$key]['tips'] = "{{ config('{$key}') }}";
            $data['settings'][$key] = config($key);
        }

        /** @var \Illuminate\View\Factory */
        $view = view();

        if ($view->exists('settings.'.$this->name)) {
            return $view->make('settings.'.$this->name, $data);
        }

        $views = ['', 'settings.item'];
        foreach ($data['items'] as $key => $item) {
            // settings.{group_name}.{item_key}
            $views[0] = join('.', ['settings', $this->name, str_replace('.','-',$item['key'])]);
            $$data['items'][$key]['html'] = $view->first($views, $item)->render();
        }

        return $view->first(['settings.'.$this->name.'.group', 'settings.group'], $data);
    }

    /**
     * 过滤配置数据，然后整合到当前配置环境
     *
     * @param  array $settings
     */
    protected function merge(array $settings)
    {
        $keys = array_keys($this->items);

        // 过滤配置，并合并到当前配置环境
        $default = array_fill_keys($keys, null);
        $settings = array_merge($default, Arr::only($settings, $keys));
        config($settings);
    }

    /**
     * 生成菜单项
     *
     * @return array
     */
    public function getMenuItem()
    {
        return [
            'title' => $this->title,
            'icon' => null,
            'route' => ['settings.edit', $this->name],
            'children' => [],
        ];
    }

    public function __get($name)
    {
        return $this->$name ?? null;
    }
}
