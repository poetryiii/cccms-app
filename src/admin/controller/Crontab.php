<?php

declare(strict_types=1);

namespace app\admin\controller;

use cccms\Base;
use think\Validate;
use Fairy\HttpCrontab;
use cccms\services\CrontabService;

/**
 * 任务管理
 * @sort 999
 */
class Crontab extends Base
{
    /**
     * 任务列表
     * @auth false
     * @login false
     * @encode json|jsonp|xml
     * @methods GET
     */
    public function index()
    {
        $params = $this->request->get(['limit', 'page', 'filter', 'op', 'title' => '']);
        $params['filter'] = json_encode(['title' => '%' . $params['title'] . '%']);
        $params = http_build_query($params);
        $response = CrontabService::instance()->httpRequest(HttpCrontab::INDEX_PATH . '?' . $params);
        if ($response['ok']) {
            $data = [
                'code' => 200,
                'msg' => 'success',
                'data' => $response['data'],
            ];
        } else {
            $data = [
                'code' => 403,
                'msg' => $response['msg'],
                'data' => [],
            ];
        }
        $pingResponse = CrontabService::instance()->httpRequest(HttpCrontab::PING_PATH);
        $data['data']['run'] = $pingResponse['ok'] ? 1 : 0;
        $data['data']['type'] = [
            '',
            'command(命令任务)',
            'class(类任务 执行execute方法)',
            'url(执行url地址任务)',
            'shell(执行shell脚本任务)',
            'sql(执行sql语句任务)',
        ];
        _result($data, _getEnCode());
    }

    /**
     * 修改任务
     * @auth true
     * @login true
     * @encode json|jsonp|xml
     * @methods PUT
     */
    public function update()
    {
        $params = $this->request->put(['id', 'field', 'value']);
        $response = CrontabService::instance()->httpRequest(HttpCrontab::EDIT_PATH . '?' . http_build_query($params), 'POST', $params);
        if ($response['ok']) {
            _result(['code' => 200, 'msg' => '修改成功'], _getEnCode());
        } else {
            _result(['code' => 403, 'msg' => $response['msg']], _getEnCode());
        }
    }

    /**
     * 修改属性
     * @auth true
     * @login true
     * @encode json|jsonp|xml
     * @methods PUT
     */
    public function modify()
    {
        $params = $this->request->put();
        $params = [
            'id' => $params['id'],
            'field' => 'status',
            'value' => $params['status'],
        ];
        if (!in_array($params['field'], ['status', 'sort', 'remark', 'title', 'rule'])) {
            _result(['code' => 403, 'msg' => '该字段不允许修改：' . $params['field']], _getEnCode());
        }
        $response = CrontabService::instance()->httpRequest(HttpCrontab::MODIFY_PATH, 'POST', $params);
        if ($response['ok']) {
            _result(['code' => 200, 'msg' => '修改成功'], _getEnCode());
        } else {
            _result(['code' => 403, 'msg' => $response['msg']], _getEnCode());
        }
    }

    /**
     * 添加任务
     * @auth true
     * @login true
     * @encode json|jsonp|xml
     * @methods POST
     */
    public function create()
    {
        $params = $this->request->post();
        $validate = new Validate;
        $validateRes = $validate->rule([
            'title|标题' => 'require',
            'rule|任务执行表达式' => 'require',
            'target|任务调用目标字符串' => 'require',
        ])->check($params);
        if (!$validateRes) {
            _result(['code' => 403, 'msg' => $validate->getError()], _getEnCode());
        }
        $params['rule'] = CrontabService::instance()->timestampConvertCrontab($params['rule']);
        $response = CrontabService::instance()->httpRequest(HttpCrontab::ADD_PATH, 'POST', $params);
        if ($response['ok']) {
            _result(['code' => 200, 'msg' => '保存成功'], _getEnCode());
        } else {
            _result(['code' => 403, 'msg' => $response['msg']], _getEnCode());
        }
    }

    /**
     * 删除任务
     * @auth true
     * @login true
     * @encode json|jsonp|xml
     * @methods DELETE
     */
    public function delete()
    {
        $id = $this->request->delete('id/d');
        $response = CrontabService::instance()->httpRequest(HttpCrontab::DELETE_PATH, 'POST', ['id' => $id]);
        if ($response['ok']) {
            _result(['code' => 200, 'msg' => '删除成功'], _getEnCode());
        } else {
            _result(['code' => 403, 'msg' => $response['msg']], _getEnCode());
        }
    }

    /**
     * 任务日志
     * @auth true
     * @login true
     * @encode json|jsonp|xml
     * @methods GET
     */
    public function flow()
    {
        $params = $this->request->get(['limit', 'page', 'filter', 'op', 'id']);
        $params['filter'] = json_encode(['crontab_id' => $params['id']]);
        $params = http_build_query($params);
        $response = CrontabService::instance()->httpRequest(HttpCrontab::FLOW_PATH . '?' . $params);
        if ($response['ok']) {
            $data = [
                'code' => 200,
                'msg' => 'success',
                'data' => $response['data'],
            ];
        } else {
            $data = [
                'code' => 403,
                'msg' => $response['msg'],
                'data' => [],
            ];
        }
        _result($data, _getEnCode());
    }

    /**
     * 重启任务
     * @auth true
     * @login true
     * @encode json|jsonp|xml
     * @methods GET
     */
    public function reload()
    {
        $id = $this->request->get('id/d');
        $response = CrontabService::instance()->httpRequest(HttpCrontab::RELOAD_PATH, 'POST', ['id' => $id]);
        if ($response['ok']) {
            _result(['code' => 200, 'msg' => '重启成功'], _getEnCode());
        } else {
            _result(['code' => 403, 'msg' => $response['msg']], _getEnCode());
        }
    }

    /**
     * 立即执行
     * @auth true
     * @login true
     * @encode json|jsonp|xml
     * @methods GET
     */
    public function run()
    {
        $id = $this->request->get('id/d');
        $response = CrontabService::instance()->httpRequest(HttpCrontab::RUNONE_PATH, 'POST', ['id' => $id]);
        if ($response['ok']) {
            _result(['code' => 200, 'msg' => '执行成功'], _getEnCode());
        } else {
            _result(['code' => 403, 'msg' => $response['msg']], _getEnCode());
        }
    }
}
