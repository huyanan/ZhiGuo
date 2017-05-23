<?php
namespace app\admin\controller;
//use app\admin\model\Administrator as AdministratorModel;
use app\admin\model\Administrator;
use app\admin\controller\AdminAuth;
use think\Validate;
use think\Controller;
use think\Model;

use app\admin\model\Users;
use app\admin\model\Stores;
use app\admin\model\Companys;

//权限认证
class CompanysController extends AdminAuth {

    //模块基本信息
    private $data = array(
        'module_name' => '客户',
        'module_url'  => '/admin/companys/',
        'module_slug' => 'companys',
        'upload_path' => UPLOAD_PATH,
        'upload_url'  => '/public/uploads/',
        'ckeditor'    => array(
            'id'     => 'ckeditor_post_content',
            //Optionnal values
            'config' => array(
                'width'  => "100%", //Setting a custom width
                'height' => '400px',
                // 默认调用 Standard Package，以下代码为调用自定义工具栏，这些基础的主要用于前台用户富文本设置
                // 'toolbar'   =>  array(  //Setting a custom toolbar
                //     array('Source','-','Save','NewPage','DocProps','Preview','Print','-','Templates'),
                //     array('Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo'),
                //     array('Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat'),
                //     array('Styles','Format','Font','FontSize'),
                //     array('TextColor','BGColor')
                // )
            )
        ),
    );


    /**
     * [index 获取客户数据列表]
     * @return [type] [description]
     */
    public function index()
    {
        /*
        *   关联查询admin nickname
        *   Model 中设置了 getPostAuthorAttr 属性读取器，所以不需要用关联查询，
        *   或者可以取消属性读取器，用关联查询，但是由于没有设置属性读取器，
        *   在 create/read 页面,select/checkbox/radio字段默认值判断时不对，需要单独设置默认值
        */
        // $list =  Companys::view('companys','*')
        //                 ->view('administrator',['nickname'],'companys.post_author=administrator.id') //这里本人对关联查询写法不熟，手册中关联查询部分没有完整实例，试了几种方法（join(),model定义关联），最后用view写
        //                 ->where('companys.status','>=','0')
        //                 ->order('companys.create_time', 'DESC')
        //                 ->paginate();

        //直接查询,注：getPostAuthorAttr 中已经得到了 post_author 名称
        $request = request();
        $param = $request->param();

//        $map['status'] = ['>=','0'];
        $map = [];

        if(!empty($param)){
            $this->data['search'] = $param;
            if(isset($param['name'])){
                $map['name'] = ['like','%'.$param['name'].'%'];
            }
        }


        $list =  Companys::where($map)
            ->order('created_at', 'DESC')
            ->paginate();

        $this->assign('data',$this->data);
        $this->assign('list',$list);
        return $this->fetch();
    }

    /**
     * [create 创建客户数据页面]
     * @return [type] [description]
     */
    public function create()
    {
//        $admins = Administrator::where('status',1)->column('nickname','id');
        $this->data['edit_fields'] = array(
            'name'     => array('type' => 'text', 'label' => '公司名'),
        );

        //默认值设置
        $item['created_at']    = date('Y-m-d H:i:s');

        $this->assign('item',$item);
        $this->assign('data',$this->data);
        return view();
    }

    /**
     * [add 新增客户数据ACTION，create()页面表单数据提交到这里]
     * @return [type] [description]
     */
    public function add()
    {
        $companys = new Companys;
        $data = input('post.');

        $rule = [
            'name|公司名' => 'require',
        ];
        // 数据验证
        $validate = new Validate($rule);
        $result   = $validate->check($data);
        if(!$result){
            return  $validate->getError();
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        if ($id = $companys->validate(true)->insertGetId($data)) {
            return $this->success('客户添加成功',$this->data['module_url']);
        } else {
            return $this->error($companys->getError());
        }
    }

    /**
     * [read 读取客户数据]
     * @param  string $id [客户ID]
     * @return [type]     [description]
     */
    public function read($id='')
    {
        $this->data['edit_fields'] = array(
            'name'           => array('type' => 'text', 'label' => '公司名'),
            'created_at'    => array('type' => 'text', 'label' => '发布时间','class'=>'datepicker','extra'=>array('data'=>array('format'=>'YYYY-MM-DD hh:mm:ss'),'wrapper'=>'col-sm-4')),
            'updated_at'    => array('type' => 'text', 'label' => '更新时间','disabled'=>true, 'extra'=>array('wrapper'=>'col-sm-4')),
        );

        //默认值设置
        $item = Companys::get($id);
//        $item['post_content'] = str_replace('&', '&amp;', $item['post_content']);

        $this->assign('item',$item);
        $this->assign('data',$this->data);

        return view();
    }

    /**
     * [update 更新客户数据，read()提交表单数据到这里]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function update($id)
    {
        $companys = new Companys;
        $data = input('post.');

        $rule = [
            //字段验证
            'name|公司名' => 'require',
        ];
        $msg = [];

        // 数据验证
        $validate = new Validate($rule,$msg);
        $result   = $validate->check($data);
        if(!$result){
            return  $validate->getError();
        }

        $data['id'] = $id;

        if ($companys->update($data)) {
            return $this->success('信息更新成功',$this->data['module_url']);
        } else {
            return $companys->getError();
        }
    }

    /**
     * [delete 删除客户数据(伪删除)]
     * @param  [type] $id [表ID]
     * @return [type]     [description]
     */
    public function delete($id)
    {
        // 真.删除，不想用伪删除，请用这段(TODO：增加回收站功能用，在回收站清空时用真删除)
        $companys = Companys::get($id);
        if ($companys) {
            $companys->delete();
            $data['id'] = $companys->id;
            $data['error'] = 0;
         $data['msg'] = '删除成功';
        } else {
         $data['error'] = 1;
         $data['msg'] = '删除失败';
        }
        return $data;
    }

}
