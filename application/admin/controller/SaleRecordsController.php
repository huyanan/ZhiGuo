<?php
namespace app\admin\controller;
use app\admin\model\Administrator;
use app\admin\controller\AdminAuth;
use app\admin\model\SaleRecords;
use app\admin\model\Goods;
use app\admin\model\Users;
use app\admin\model\Stores;
use app\admin\model\Companys;
use think\Controller;
use think\Validate;
use think\Image;
use think\Request;

class SaleRecordsController extends Controller
{
    //模块基本信息
    private $data = array(
        'module_name' => '销售记录',
        'module_url'  => '/admin/sale_records/',
        'module_slug' => 'saleRecords',
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
        // $list =  SaleRecords::view('saleRecords','*')
        //                 ->view('administrator',['nickname'],'saleRecords.post_author=administrator.id') //这里本人对关联查询写法不熟，手册中关联查询部分没有完整实例，试了几种方法（join(),model定义关联），最后用view写
        //                 ->where('saleRecords.status','>=','0')
        //                 ->order('saleRecords.created_at', 'DESC')
        //                 ->paginate();

        //直接查询,注：getPostAuthorAttr 中已经得到了 post_author 名称
        $request = request();
        $param = $request->param();

        // $map['status'] = ['>=','0'];
        $map = [];

        $companys = Companys::where('id', '<>', '-1')->column('name', 'id');
            $stores = Stores::where('id', '<>', '-1')->column('name', 'id');
        if(!empty($param)){
            $this->data['search'] = $param;
            if(isset($param['company_name'])){
                $map['company_name'] = ['like','%'.$param['company_name'].'%'];
            }
            if(isset($param['store_name'])){
                $map['store_name'] = ['like','%'.$param['store_name'].'%'];
            }
            if(isset($param['name'])){
                $map['name'] = ['like','%'.$param['name'].'%'];
            }
            if(isset($param['telephone'])){
                $map['telephone'] = ['like','%'.$param['telephone'].'%'];
            }
            if(isset($param['goods_name'])){
                $map['goods_name'] = ['like','%'.$param['goods_name'].'%'];
            }
            if(isset($param['goods_type'])){
                $map['goods_type'] = ['like','%'.$param['goods_type'].'%'];
            }
            if(isset($param['goods_number'])){
                $map['goods_number'] = ['like','%'.$param['goods_number'].'%'];
            }
            if(isset($param['start_time']) && $param['start_time'] != '' && isset($param['end_time']) && $param['end_time'] != ''){
                $map['created_at'] = ['between time',[$param['start_time'],$param['end_time']]];
            }

            if(isset($param['start_time']) && $param['start_time'] != '' && !$param['end_time']){
                $map['created_at'] = ['>= time',$param['start_time']];
            }
            if(isset($param['end_time']) && $param['end_time'] != '' && !$param['start_time']){
                $map['created_at'] = ['<= time',$param['end_time']];
            }
        }


        $list =  SaleRecords::where($map)
                        ->order('created_at', 'DESC')
                        ->paginate();

        // foreach ($list as $key => $value) {
        //     if (!$value->goods_type || !$value->goods_name) {
        //         $goods = Goods::where('goods_number' ,$value->goods_number)->find();
        //         if ($goods) {
        //             $value->goods_type = $goods->type;
        //             $value->goods_name = $goods->name;
        //             if ($value->save()) {
                        
        //             }
        //         }
        //     }
        // }

        $this->assign('data',$this->data);
        $this->assign('list',$list);
        $this->assign('companys',$companys);
        $this->assign('stores',$stores);
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
            'post_title'     => array('type' => 'text', 'label' => '标题'),
            'post_excerpt'   => array('type' => 'textarea', 'label' => '摘要'),
            'post_content'   => array('type' => 'textarea', 'label' => '内容','id'=>'ckeditor_post_content'),
            'feature_image'  => array('type' => 'file','label'     => '特色图片'),
            'status'         => array('type' => 'radio', 'label' => '状态','default'=> array(-1 => '删除', 0 => '草稿', 1 => '发布',2 => '待审核')),
            'hr1'            => array('type' => 'hr'),
            'alert1'         => array('type' => 'alert', 'default' => '其它信息'),
            'post_author'    => array('type' => 'select', 'label' => '作者','default' => $admins, 'extra'=>array('wrapper'=>'col-sm-3')),
            'post_password'  => array('type' => 'text', 'label' => '访问密码','notes'=>'默认不填则可以直接访问', 'extra'=>array('wrapper'=>'col-sm-3')),
            'comment_status' => array('type' => 'select', 'label' => '评论开关', 'default' => array('opened'=>'打开','closed' => '关闭'), 'extra'=>array('wrapper'=>'col-sm-3')),
            'created_at'    => array('type' => 'text', 'label' => '发布时间','class'=>'datepicker','extra'=>array('data'=>array('format'=>'YYYY-MM-DD hh:mm:ss'),'wrapper'=>'col-sm-3')),
            'hr2'            => array('type' => 'hr'),
        );

        //默认值设置
        $item['status']         = '发布';
        $item['comment_status'] = config('comment_toggle') ? '打开' : '关闭';
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
        $data = input('post.');

        $qrcode = $data['qrcode'];

        // 首先判断是条形码还是二维码
        // 如果字符串中包涵EAN_13说明是条形码
        $type = 'barcode';

        if (strpos($qrcode, 'EAN_13') !== false) {
            $type = 'barCode';
            $goods_number = substr($qrcode, 7);
        } else {
            $type = 'qrcode';
            $goods_number = explode('-', $qrcode)[0];
        }


        $goods = Goods::get(['goods_number' => $goods_number]);
        if (!$goods) {
            return [
                'code' => -1,
                'msg' => '货物不存在'
            ];
        }

        if ($type == 'qrcode') {
            $saleRecords = SaleRecords::get(['qrcode' => $qrcode]);
            if ($saleRecords) {
                return [
                    'code' => -1,
                    'msg' => '二维码不能重复扫描'
                ];
            }
        }

        $uid = session('w_uid');

        $user = Users::get(['weixin_openid' => $uid]);
        if (!$user->telephone || !$user->name || !$user->store_id || !$user->company_name) {
            return [
                'code' => -1,
                'msg' => '请完善用户信息(姓名，手机号，公司，店面)'
            ];
        }


        $saleRecords = new SaleRecords();
        $saleRecords->owner_id = $user->id;
        $saleRecords->name = $user->name;
        $saleRecords->telephone = $user->telephone;
        $saleRecords->company_name = $user->company_name;
        $saleRecords->store_name = $user->store ? $user->store->name : '';
        $saleRecords->qrcode = $qrcode;
        $saleRecords->goods_number = $goods_number;
        if ($goods->name) {
            $saleRecords->goods_name = $goods->name;
        }
        if ($goods->type) {
            $saleRecords->goods_type = $goods->type;
        }


        $saleRecords->created_at = time();
        $saleRecords->updated_at = time();


        if ($saleRecords->save()) {
            return [
                'code' => 0,
                'msg' => '创建销售记录成功！'
            ];
        } else {
            return $this->error($saleRecords->getError());
        }
    }

    // public function checkUser($user) {
    //     if (!$user) {
    //         return [
    //             'valid' => false,
    //             'msg' => '用户不存在'
    //         ];
    //     }
    //     if (!$user->company_name) {
    //         return []
    //     }
    // }


    /**
     * [read 读取文章数据]
     * @param  string $id [文章ID]
     * @return [type]     [description]
     */
    public function read($id='')
    {

        $companys = Companys::where('id', '<>', -1)->column('name', 'id');

        // $stores = Stores::where('id', '<>', -1)->column('name', 'id');

        $stores = Stores::where('id', '<>', '-1')->select();
        $stores_map = [];
        foreach ($stores as $skey => $store) {
            if (!isset($stores_map[$store->company_name])) {
                $stores_map[$store->company_name] = [];
            }
            $stores_map[$store->company_name][] = $store;
        }

        $this->data['edit_fields'] = array(
            'owner_id'     => array('type' => 'text', 'label' => '销售员ID'),
            'name'   => array('type' => 'text', 'label' => '销售员姓名'),
            'telephone'   => array('type' => 'text', 'label' => '销售员手机号'),
            'qrcode'  => array('type' => 'text','label'     => '二维码/条形码'),
            'goods_name'         => array('type' => 'text', 'label' => '货物名称'),
            'goods_type'         => array('type' => 'text', 'label' => '货物型号'),
            'goods_number'         => array('type' => 'text', 'label' => '货物编号'),
            // 'company_name'    => array('type' => 'select', 'label' => '公司名','default' => $companys, 'extra'=>array('wrapper'=>'col-sm-4')),
            // 'store_name'         => array('type' => 'select', 'label' => '店面名','default' => $stores, 'extra'=>array('wrapper'=>'col-sm-4')),
        );

        //默认值设置
        $item = SaleRecords::get($id);

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
        $saleRecords = new SaleRecords;
        $data = input('post.');

        $rule = [
            //字段验证
            'owner_id|销售员ID' => 'require',
            'name|销售员姓名' => 'require',
            'telephone|销售员手机号' => 'require',
            'qrcode|二维码/条形码' => 'require',
            // 'goods_name|货物名称' => 'require',
            // 'goods_type|货物型号' => 'require',
            'goods_number|货物编号' => 'require',
            'company_name|公司名' => 'require',
            'store_name|店面名' => 'require',
        ];
        $msg = [];

        // 数据验证
        $validate = new Validate($rule,$msg);
        $result   = $validate->check($data);
        if(!$result){
            return  $validate->getError();
        }

        $data['id'] = $id;

        if ($saleRecords->update($data)) {
            return $this->success('信息更新成功',$this->data['module_url'].$id);
        } else {
            return $saleRecords->getError();
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
        $saleRecords = SaleRecords::get($id);
        if ($saleRecords) {
            $saleRecords->delete();
            $data['id'] = $saleRecords->id;
            $data['error'] = 0;
         $data['msg'] = '删除成功';
        } else {
         $data['error'] = 1;
         $data['msg'] = '删除失败';
        }
        return $data;
    }

    public function delete_image($id){
        $saleRecords = SaleRecords::get($id);
        if (file_exists($this->data['upload_path'] .'/'. $saleRecords->feature_image)) {
            @unlink($this->data['upload_path'] .'/'. $saleRecords->feature_image);
        }

        $source_image = str_replace('_thumb', '', $saleRecords->feature_image);
        if (file_exists($this->data['upload_path'] .'/'. $source_image)) {
            @unlink($this->data['upload_path'] .'/'. $source_image);
        }

        $data['id'] = $id;
        $data['feature_image'] = '';
        if ($saleRecords->update($data)) {
            return $this->success('图像删除成功',$this->data['module_url'].$id);
        }else{
            return $saleRecords->getError();
        }


    }
}