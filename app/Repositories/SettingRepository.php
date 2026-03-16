<?php

namespace App\Repositories;

use App\Models\Setting;

class SettingRepository extends BaseRepository
{
    public function __construct(Setting $model)
    {
        parent::__construct($model);
    }

    public function getByKey($key, $default = null)
    {
        return Setting::get($key, $default);
    }

    public function setByKey($key, $value, $type = 'string')
    {
        return Setting::set($key, $value, $type);
    }

    public function getAllSettings()
    {
        return $this->model->pluck('value', 'key');
    }
}
