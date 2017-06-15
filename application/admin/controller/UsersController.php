<?php
namespace app\admin\controller;
//use app\admin\model\Administrator as AdministratorModel;
use app\admin\model\Administrator;
use app\admin\controller\AdminAuth;
use think\Controller;
use think\Model;

use think\Validate;
use app\admin\model\Users;
use app\admin\model\Companys;
use app\admin\model\Stores;


//权限认证
class UsersController extends Controller {

    //模块基本信息
    private $data = array(
        'module_name' => '销售员',
        'module_url'  => '/admin/users/',
        'module_slug' => 'users',
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
        // $list =  Users::view('users','*')
        //                 ->view('administrator',['nickname'],'users.post_author=administrator.id') //这里本人对关联查询写法不熟，手册中关联查询部分没有完整实例，试了几种方法（join(),model定义关联），最后用view写
        //                 ->where('users.status','>=','0')
        //                 ->order('users.create_time', 'DESC')
        //                 ->paginate();

        //直接查询,注：getPostAuthorAttr 中已经得到了 post_author 名称
        $request = request();
        $param = $request->param();

//        $map['status'] = ['>=','0'];
        $map = [];
        $companys = Companys::where('id', '<>', '-1')->column('name', 'id');
            $stores = Stores::where('id', '<>', '-1')->select();
        $stores_map = [];
        foreach ($stores as $skey => $store) {
            if (!isset($stores_map[$store->company_name])) {
                $stores_map[$store->company_name] = [];
            }
            $stores_map[$store->company_name][] = $store;
        }
        if(!empty($param)){
            $this->data['search'] = $param;
            if(isset($param['company_name'])){
                $map['company_name'] = ['like','%'.$param['company_name'].'%'];
            }
            if(isset($param['name'])){
                $map['name'] = ['like','%'.$param['name'].'%'];
            }
            if(isset($param['telephone'])){
                $map['telephone'] = ['like','%'.$param['telephone'].'%'];
            }

            if(isset($param['store_id'])){
                $map['store_id'] = ['=',$param['store_id']];
            }

            if(isset($param['province'])){
                $map['province'] = ['like','%'.$param['province'].'%'];
            }

            if(isset($param['city'])){
                $map['city'] = ['like','%'.$param['city'].'%'];
            }
        }


        $list =  Users::where($map)
            ->order('created_at', 'DESC')
            ->paginate();

        $this->assign('data',$this->data);
        $this->assign('data_json', json_encode($this->data));
        $this->assign('list',$list);
        $this->assign('companys',$companys);
        $this->assign('stores',$stores);
        $this->assign('stores_map', json_encode($stores_map));
        return $this->fetch();
    }

    /**
     * [create 创建文章数据页面]
     * @return [type] [description]
     */
    public function create()
    {
        $admins = Administrator::where('status',1)->column('nickname','id');

        $this->data['edit_fields'] = array(
            'name'     => array('type' => 'text', 'label' => '姓名'),
            'telephone'   => array('type' => 'text', 'label' => '电话'),
            'store_id'   => array('type' => 'text', 'label' => '实体店'),
            'province'  => array('type' => 'text','label'     => '省'),
            'city'         => array('type' => 'text', 'label' => '市'),

        );

        //默认值设置
        $item['create_time']    = date('Y-m-d H:i:s');

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
        $users = new Users;
        $data = input('post.');

        $rule = [
            'name|姓名' => 'require',
            'telephone|电话' => 'require',
            'store_id|实体店' => 'require',
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


        if ($id = $users->validate(true)->insertGetId($data)) {
            return $this->success('销售员添加成功',$this->data['module_url']);
        } else {
            return $this->error($users->getError());
        }
    }



    /**
     * [read 读取文章数据]
     * @param  string $id [文章ID]
     * @return [type]     [description]
     */
    public function read($id='')
    {
        $companys = Companys::where('id', '<>', '-1')->column('name', 'id');
        // $stores = Stores::where('id', '<>', '-1')->column('name', 'id');

        $stores = Stores::where('id', '<>', '-1')->select();
        $stores_map = [];
        foreach ($stores as $skey => $store) {
            if (!isset($stores_map[$store->company_name])) {
                $stores_map[$store->company_name] = [];
            }
            $stores_map[$store->company_name][] = $store;
        }

        $this->data['edit_fields'] = array(
            'name'     => array('type' => 'text', 'label' => '姓名'),
            'telephone'   => array('type' => 'text', 'label' => '手机号'),
            // 'company_name'    => array('type' => 'select', 'label' => '公司名','default' => $companys, 'extra'=>array('wrapper'=>'col-sm-4')),
            // 'store_id'    => array('type' => 'select', 'label' => '店面名','default' => $stores, 'extra'=>array('wrapper'=>'col-sm-4')),
            // 'province'  => array('type' => 'text','label'     => '省'),
            // 'city'         => array('type' => 'text','label'     => '市'),
        );

        //默认值设置
        $item = Users::get($id);
//        $item['post_content'] = str_replace('&', '&amp;', $item['post_content']);
        $this->assign('item',$item);
        $this->assign('data',$this->data);
        $this->assign('companys', $companys);
        $this->assign('stores', $stores);
        $this->assign('stores_map', json_encode($stores_map));

        return view();
    }

    /**
     * [update 更新文章数据，read()提交表单数据到这里]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function update($id)
    {
        $users = Users::get($id);
        $data = input('post.');
        $rule = [
            //字段验证
            // 'post_title|文章标题' => 'require',
            // 'status|文章状态' => 'require',
            // 'post_author|文章作者' => 'require',
            // 'comment_status|评论开关' => 'require',
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

        if ($users->update($data)) {
            return $this->success('信息更新成功',$this->data['module_url']);
        } else {
            return $users->getError();
        }
    }

    public function updateById($id)
    {
        $users = Users::get($id);
        $data = input('post.');
        
        $data['id'] = $id;

        if ($users->update($data)) {
            return $this->success('信息更新成功','/');
        } else {
            return $users->getError();
        }
    }

    /**
     * [upload 图片上传]
     * @return [type] [description]
     */
    public function upload(){
        // 获取表单上传文件
        $file = request()->file('feature_image');
        if($file){
            if (true !== $this->validate(['feature_image' => $file], ['feature_image' => 'image'])) {
                $this->error('请选择图像文件');
            } else {
                $info = $file->rule('uniqid')->move(ROOT_PATH . 'public' . DS . 'uploads'); //保存原图

                // 读取图片
                $image = Image::open($file);
                // 图片处理
                $image_type = request()->param('type') ? request()->param('type') : 1;
                switch ($image_type) {
                    case 1: // 缩略图
                        $image->thumb(150, 150, Image::THUMB_CENTER);
                        break;
                    case 2: // 图片裁剪
                        $image->crop(300, 300);
                        break;
                    case 3: // 垂直翻转
                        $image->flip();
                        break;
                    case 4: // 水平翻转
                        $image->flip(Image::FLIP_Y);
                        break;
                    case 5: // 图片旋转
                        $image->rotate();
                        break;
                    case 6: // 图片水印
                        $image->water(ROOT_PATH . 'public/static/images/logo.png', Image::WATER_NORTHWEST, 50);
                        break;
                    case 7: // 文字水印
                        $image->text('ThinkPHP', VENDOR_PATH . 'topthink/think-captcha/assets/ttfs/1.ttf', 20, '#ffffff');
                        break;
                }
                $this->sourceFile = $info->getFilename();

                $fileName = explode('.',$info->getFilename());
                $saveName = $fileName[0] . '_thumb.' .$info->getExtension();
                $image->save($this->data['upload_path'] .'/'. $saveName);

                $this->imageThumbName = $saveName;

                return $this->imageThumbName;
            }
        }else{
            return false;
        }
    }

    /**
     * [delete 删除文章数据(伪删除)]
     * @param  [type] $id [表ID]
     * @return [type]     [description]
     */
    public function delete($id)
    {

        // 真.删除，不想用伪删除，请用这段(TODO：增加回收站功能用，在回收站清空时用真删除)
        $users = Users::get($id);
        if ($users) {
            $users->delete();
            $data['id'] = $users->id;
            $data['error'] = 0;
        	$data['msg'] = '删除成功';
        } else {
        	$data['error'] = 1;
        	$data['msg'] = '删除失败';
        }
        return $data;
    }

    public function delete_image($id){
        $users = Users::get($id);
        if (file_exists($this->data['upload_path'] .'/'. $users->feature_image)) {
            @unlink($this->data['upload_path'] .'/'. $users->feature_image);
        }

        $source_image = str_replace('_thumb', '', $users->feature_image);
        if (file_exists($this->data['upload_path'] .'/'. $source_image)) {
            @unlink($this->data['upload_path'] .'/'. $source_image);
        }

        $data['id'] = $id;
        $data['feature_image'] = '';
        if ($users->update($data)) {
            return $this->success('图像删除成功',$this->data['module_url'].$id);
        }else{
            return $users->getError();
        }


    }


}
