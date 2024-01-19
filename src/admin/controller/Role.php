<?php
declare(strict_types=1);

namespace app\admin\controller;

use cccms\Base;
use cccms\extend\ArrExtend;
use cccms\model\{SysRole, SysAuth};
use cccms\services\{NodeService, AuthService, UserService};

/**
 * 角色管理
 * @sort 996
 */
class Role extends Base
{
    public function init(): void
    {
        $this->model = SysRole::mk();
    }

    /**
     * 添加角色
     * @auth true
     * @login true
     * @encode json|jsonp|xml
     * @methods POST
     */
    public function create(): void
    {
        $this->model->create(_validate('put.sys_role.true', 'role_name'));
        _result(['code' => 200, 'msg' => '添加成功'], _getEnCode());
    }

    /**
     * 删除角色
     * @auth true
     * @login true
     * @encode json|jsonp|xml
     * @methods DELETE
     */
    public function delete(): void
    {
        $this->model->_delete($this->request->delete('id/d', 0));
        _result(['code' => 200, 'msg' => '删除成功'], _getEnCode());
    }

    /**
     * 修改角色
     * @auth true
     * @login true
     * @encode json|jsonp|xml
     * @methods PUT
     */
    public function update(): void
    {
        $this->model->update(_validate('put.sys_role.true', 'id|nodes'));
        _result(['code' => 200, 'msg' => '更新成功'], _getEnCode());
    }

    /**
     * 角色列表
     * @auth true
     * @login true
     * @encode json|jsonp|xml
     * @methods GET
     */
    public function index(): void
    {
        $roles = $this->model->with('nodesRelation')->_list(callable: function ($data) {
            return array_map(function ($item) {
                $item['nodes'] = array_column($item['nodesRelation'], 'node');
                unset($item['nodesRelation']);
                return $item;
            }, $data->toArray());
        });
        _result(['code' => 200, 'msg' => 'success', 'data' => [
            'fields' => AuthService::instance()->fields('sys_role'),
            'nodes' => NodeService::instance()->getAuthNodesTree(),
            'data' => ArrExtend::toTreeList($roles, 'id', 'role_id')
        ]], _getEnCode());
    }
}
