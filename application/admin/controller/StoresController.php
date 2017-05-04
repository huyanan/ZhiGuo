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

//权限认证
class StoresController extends Controller {

    //模块基本信息
    private $data = array(
        'module_name' => '实体店',
        'module_url'  => '/admin/stores/',
        'module_slug' => 'stores',
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
     * [index 获取文章数据列表]
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
        // $list =  Posts::view('posts','*')
        //                 ->view('administrator',['nickname'],'posts.post_author=administrator.id') //这里本人对关联查询写法不熟，手册中关联查询部分没有完整实例，试了几种方法（join(),model定义关联），最后用view写
        //                 ->where('posts.status','>=','0')
        //                 ->order('posts.create_time', 'DESC')
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

            if(isset($param['province'])){
                $map['province'] = ['like','%'.$param['province'].'%'];
            }

            if(isset($param['city'])){
                $map['city'] = ['like','%'.$param['city'].'%'];
            }
        }


        $list =  Stores::where($map)
            ->order('created_at', 'DESC')
            ->paginate();

        $this->assign('data',$this->data);
        $this->assign('list',$list);
        return $this->fetch();
    }

    /**
     * [create 创建文章数据页面]
     * @return [type] [description]
     */
    public function create()
    {
//        $admins = Administrator::where('status',1)->column('nickname','id');
        $this->data['edit_fields'] = array(
            'name'     => array('type' => 'text', 'label' => '店名'),
            'province'     => array('type' => 'text', 'label' => '省'),
            'city'     => array('type' => 'text', 'label' => '市'),
        );

        //默认值设置
        $item['created_at']    = date('Y-m-d H:i:s');

        $this->assign('item',$item);
        $this->assign('data',$this->data);
        return view();
    }

    /**
     * [add 新增文章数据ACTION，create()页面表单数据提交到这里]
     * @return [type] [description]
     */
    public function add()
    {
        $stores = new Stores;
        $data = input('post.');

        $rule = [
            'name|店名' => 'require',
            'province|省' => 'require',
            'city|市' => 'require',
        ];
        // 数据验证
        $validate = new Validate($rule);
        $result   = $validate->check($data);
        if(!$result){
            return  $validate->getError();
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        if ($id = $stores->validate(true)->insertGetId($data)) {
            return $this->success('实体店添加成功',$this->data['module_url'].$id);
        } else {
            return $this->error($stores->getError());
        }
    }

    /**
     * [read 读取文章数据]
     * @param  string $id [文章ID]
     * @return [type]     [description]
     */
    public function read($id='')
    {
        $this->data['edit_fields'] = array(
            'name'           => array('type' => 'text', 'label' => '店名'),
            'province'       => array('type' => 'text', 'label' => '省'),
            'city'           => array('type' => 'text', 'label' => '市'),
            'created_at'    => array('type' => 'text', 'label' => '发布时间','class'=>'datepicker','extra'=>array('data'=>array('format'=>'YYYY-MM-DD hh:mm:ss'),'wrapper'=>'col-sm-4')),
            'updated_at'    => array('type' => 'text', 'label' => '更新时间','disabled'=>true, 'extra'=>array('wrapper'=>'col-sm-4')),
        );

        //默认值设置
        $item = Stores::get($id);
//        $item['post_content'] = str_replace('&', '&amp;', $item['post_content']);

        $this->assign('item',$item);
        $this->assign('data',$this->data);

        return view();
    }

    /**
     * [update 更新文章数据，read()提交表单数据到这里]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function update($id)
    {
        $posts = new Posts;
        $data = input('post.');

        $rule = [
            //字段验证
            'post_title|文章标题' => 'require',
            'status|文章状态' => 'require',
            'post_author|文章作者' => 'require',
            'comment_status|评论开关' => 'require',
        ];
        $msg = [];

        // 数据验证
        $validate = new Validate($rule,$msg);
        $result   = $validate->check($data);
        if(!$result){
            return  $validate->getError();
        }

        $data['id'] = $id;

        $data['feature_image'] = $this->upload();
        if(!$data['feature_image']){
            unset($data['feature_image']);
        }

        if ($posts->update($data)) {
            return $this->success('信息更新成功',$this->data['module_url'].$id);
        } else {
            return $posts->getError();
        }
    }

}
