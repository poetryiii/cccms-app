<?php
declare(strict_types=1);

namespace app\admin\controller;

use cccms\{Base, Storage};
use cccms\model\{SysFile, SysFileCate};
use cccms\services\{AuthService, TypesService, UploadService};

/**
 * 附件管理
 * @sort 991
 */
class File extends Base
{
    public function init()
    {
        $this->model = SysFile::mk();
    }

    /**
     * 添加附件
     * @auth true
     * @login true
     * @encode json|jsonp|xml
     * @methods POST
     */
    public function create()
    {
        $cate_id = $this->request->post('cate_id/d', 0);
        $folderName = SysFileCate::mk()->where('id', $cate_id)->column('name') ?: 'default';
        _result([
            'code' => 200,
            'msg' => '添加成功',
            'data' => UploadService::instance()->upload($folderName)
        ], _getEnCode());
    }

    /**
     * 删除附件
     * @auth true
     * @login true
     * @encode json|jsonp|xml
     * @methods DELETE
     */
    public function delete()
    {
        Storage::instance()->delete($this->request->delete('id/d', 0));
        _result(['code' => 200, 'msg' => '删除成功'], _getEnCode());
    }

    /**
     * 修改附件
     * @auth true
     * @login true
     * @encode json|jsonp|xml
     * @methods PUT
     */
    public function update()
    {
        $this->model->update(_validate('put', ['sys_file', 'id', [
            'file_name',
            'file_desc',
            'extract_code',
            'status' => 1,
        ]]));
        _result(['code' => 200, 'msg' => '更新成功'], _getEnCode());
    }

    /**
     * 附件列表
     * @auth true
     * @login true
     * @encode json|jsonp|xml
     * @methods GET
     */
    public function index()
    {
        $cates = SysFileCate::mk()->_list();
        $cate_id = $this->request->get('cate_id/d', null);
        $cate_id = $cate_id ?: ($cates[0]['id'] ?? 0);
        $params = _validate('get.sys_file.true', 'page,limit|cate_id');
        $data = $this->model->with(['cate', 'user'])->_withSearch('cate_id', [
            'cate_id' => $params['cate_id'],
        ])->order('id desc')->_page($params);
        _result(['code' => 200, 'msg' => 'success', 'data' => [
            'fields' => AuthService::instance()->fields('sys_file'),
            'cates' => $cates,
            'total' => $data['total'],
            'data' => $data['data']
        ]], _getEnCode());
    }
}