<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;

class BaseController extends Controller
{
    public $dsp_datas;

    /**
     * 各コントローラーで使用するデータを格納する配列
     */
    public function __construct()
    {
        $constdatas = Config::get('const.dspDatas');
        foreach ($constdatas as $key => $value) {
            $this->dsp_datas[$key] = $value;
        }
        $this->dsp_datas = array_merge($this->dsp_datas, $this->createCsrf());
    }

    /**
     * CSRFトークンを生成する
     */
    private function createCsrf()
    {
        $token = csrf_token();
        return [
            'apitoken' => $token,
            'tagtoken' => '<input type="hidden" name="_token" value="' . $token . '" />' . PHP_EOL,
        ];
    }
}
