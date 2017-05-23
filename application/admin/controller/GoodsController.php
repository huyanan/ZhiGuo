<?php
namespace app\admin\controller;
//use app\admin\model\Administrator as AdministratorModel;
use app\admin\model\Administrator;
use app\admin\controller\AdminAuth;
use think\Validate;
use think\Controller;
use think\Model;

use Comodojo\Zip\Zip;
// use tcpdf\TCPDF;
require_once(AROOT . 'vendor/tecnickcom/tcpdf/tcpdf.php');


use app\admin\model\Users;
use app\admin\model\Stores;
use app\admin\model\Goods;

//权限认证
class GoodsController extends Controller {

    //模块基本信息
    private $data = array(
        'module_name' => '货物',
        'module_url'  => '/admin/goods/',
        'module_slug' => 'goods',
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
            if(isset($param['type'])){
                $map['type'] = ['like','%'.$param['type'].'%'];
            }
            if(isset($param['goods_number'])){
                $map['goods_number'] = ['like','%'.$param['goods_number'].'%'];
            }
        }


        $list =  Goods::where($map)
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
            'name'     => array('type' => 'text', 'label' => '货物名称'),
            'type'     => array('type' => 'text', 'label' => '货物型号'),
            'goods_number'     => array('type' => 'text', 'label' => '货物编号'),
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
        $goods = new Goods;
        $data = input('post.');

        $rule = [
            'name|货物名称' => 'require',
            'goods_number|货物编号' => 'require',
            'type|货物型号' => 'require',
        ];
        // 数据验证
        $validate = new Validate($rule);
        $result   = $validate->check($data);
        if(!$result){
            return  $validate->getError();
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        if ($id = $goods->validate(true)->insertGetId($data)) {
            return $this->success('货物添加成功',$this->data['module_url']);
        } else {
            return $this->error($goods->getError());
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
            'name'           => array('type' => 'text', 'label' => '货物名称'),
            'type'           => array('type' => 'text', 'label' => '货物型号'),
            'goods_number'           => array('type' => 'text', 'label' => '货物编号'),
            'created_at'    => array('type' => 'text', 'label' => '发布时间','class'=>'datepicker','extra'=>array('data'=>array('format'=>'YYYY-MM-DD hh:mm:ss'),'wrapper'=>'col-sm-4')),
            'updated_at'    => array('type' => 'text', 'label' => '更新时间','disabled'=>true, 'extra'=>array('wrapper'=>'col-sm-4')),
        );

        //默认值设置
        $item = Goods::get($id);
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
        $goods = new Goods;
        $data = input('post.');

        $rule = [
            //字段验证
            'name|货物名称' => 'require',
            'type|货物型号' => 'require',
            'goods_number|货物编号' => 'require',
        ];
        $msg = [];

        // 数据验证
        $validate = new Validate($rule,$msg);
        $result   = $validate->check($data);
        if(!$result){
            return  $validate->getError();
        }

        $data['id'] = $id;

        if ($goods->update($data)) {
            return $this->success('信息更新成功',$this->data['module_url']);
        } else {
            return $goods->getError();
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
        $goods = Goods::get($id);
        if ($goods) {
            $goods->delete();
            $data['id'] = $goods->id;
            $data['error'] = 0;
         $data['msg'] = '删除成功';
        } else {
         $data['error'] = 1;
         $data['msg'] = '删除失败';
        }
        return $data;
    }

    /**
     * [generateQrcode 批量生成二维码]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function generateQrcode($id)
    {
        set_time_limit(0);
        $data = input('get.');
        
        $qrcodeCount = isset($data['qrcodeCount']) ? intval($data['qrcodeCount']) : 1008;
        // 清空tmp
        unlink_dir(AROOT . 'tmp/');
        $goods = Goods::find($id);

        $goods_number = $goods->goods_number;

        // $qrcodeCount = 5000;
        $qrcodes = [];
        // $zip = null;
        // $zipPath = AROOT . 'tmp/' . $goods_number . '-' . time() . '-' . gen_uuid() . '.zip';
        // $zip = Zip::create($zipPath);
        for ($i=0; $i < $qrcodeCount; $i++) {
            $filename = $goods_number . '-' . time() . '-' . gen_uuid();
            $filepath = AROOT . 'tmp/' . $filename . '.png';
            $qrcodes[] = $filepath;
            \PHPQRCode\QRcode::png($goods_number . '-' . time() . '-' . gen_uuid(), $filepath, 'L', 3);
            // $zip->add($filepath);
        }
        // $zip->close();

        $this->createPdf($qrcodes);
    }


    /**
     * 二维码页面
     */
    public function qrcode()
    {
        return view();
    }


    /**
     * 创建pdf
     */
    public function createPdf($files)
    {
       // create new PDF document
       $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

       // set document information
       $pdf->SetCreator(PDF_CREATOR);
       // $pdf->SetAuthor('Nicola Asuni');
       // $pdf->SetTitle('TCPDF Example 009');
       // $pdf->SetSubject('TCPDF Tutorial');
       // $pdf->SetKeywords('TCPDF, PDF, example, test, guide');

       // // set default header data
       // $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 009', PDF_HEADER_STRING);

       // // set header and footer fonts
       // $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
       // $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

       // // set default monospaced font
       // $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

       // // set margins
       // $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
       // $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
       // $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

       // set auto page breaks
       // $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
       $pdf->SetAutoPageBreak(TRUE, 0);

       // set image scale factor
       $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

       // set some language-dependent strings (optional)
       if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
           require_once(dirname(__FILE__).'/lang/eng.php');
           $pdf->setLanguageArray($l);
       }

       // -------------------------------------------------------------------

       // add a page
       $pdf->AddPage();

       // set JPEG quality
       $pdf->setJPEGQuality(75);

       // Image method signature:
       // Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false)

       // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

       // // Example of Image from data stream ('PHP rules')
       // $imgdata = base64_decode('iVBORw0KGgoAAAANSUhEUgAAABwAAAASCAMAAAB/2U7WAAAABlBMVEUAAAD///+l2Z/dAAAASUlEQVR4XqWQUQoAIAxC2/0vXZDrEX4IJTRkb7lobNUStXsB0jIXIAMSsQnWlsV+wULF4Avk9fLq2r8a5HSE35Q3eO2XP1A1wQkZSgETvDtKdQAAAABJRU5ErkJggg==');

       // // The '@' character is used to indicate that follows an image data stream and not an image file name
       // $pdf->Image('@'.$imgdata);

       // // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

       // // Image example with resizing
       // $pdf->Image('images/image_demo.jpg', 15, 140, 75, 113, 'JPG', 'http://www.tcpdf.org', '', true, 150, '', false, false, 1, false, false, false);

       // // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

       // // test fitbox with all alignment combinations

       // $horizontal_alignments = array('L', 'C', 'R');
       // $vertical_alignments = array('T', 'M', 'B');

       // $x = 15;
       // $y = 35;
       // $w = 30;
       // $h = 30;
       // // test all combinations of alignments
       // for ($i = 0; $i < 3; ++$i) {
       //     $fitbox = $horizontal_alignments[$i].' ';
       //     $x = 15;
       //     for ($j = 0; $j < 3; ++$j) {
       //         $fitbox[1] = $vertical_alignments[$j];
       //         $pdf->Rect($x, $y, $w, $h, 'F', array(), array(128,255,128));
       //         $pdf->Image('images/image_demo.jpg', $x, $y, $w, $h, 'JPG', '', '', false, 300, '', false, false, 0, $fitbox, false, false);
       //         $x += 32; // new column
       //     }
       //     $y += 32; // new row
       // }

       // $x = 115;
       // $y = 35;
       // $w = 25;
       // $h = 50;
       // for ($i = 0; $i < 3; ++$i) {
       //     $fitbox = $horizontal_alignments[$i].' ';
       //     $x = 115;
       //     for ($j = 0; $j < 3; ++$j) {
       //         $fitbox[1] = $vertical_alignments[$j];
       //         $pdf->Rect($x, $y, $w, $h, 'F', array(), array(128,255,255));
       //         $pdf->Image('images/image_demo.jpg', $x, $y, $w, $h, 'JPG', '', '', false, 300, '', false, false, 0, $fitbox, false, false);
       //         $x += 27; // new column
       //     }
       //     $y += 52; // new row
       // }

       // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

       // Stretching, position and alignment example
       $pageWidth = $pdf->getPageWidth();
       $pageHeight = $pdf->getPageHeight();
       // var_dump($pageWidth);
       // var_dump($pageHeight);
       // exit;
       $pdf->SetXY(0, 0);
       $pdf->setLeftMargin(0);
       $pdf->setTopMargin(0);

       $imgWidth = 35;

       $inlineCount = intval($pageWidth / $imgWidth);

       for ($i=0; $i < count($files); $i++) {
            if (($i+1)%$inlineCount === 0) {
                $pdf->Image($files[$i], '', '', 35, 35, '', '', 'N', false, 300, '', false, false, 1, false, false, false);
            } else {
                $pdf->Image($files[$i], '', '', 35, 35, '', '', 'T', false, 300, '', false, false, 1, false, false, false);
            }
       }
       // $pdf->Image('images/image_demo.jpg', '', '', 40, 40, '', '', 'T', false, 300, '', false, false, 1, false, false, false);
       // $pdf->Image('images/image_demo.jpg', '', '', 40, 40, '', '', '', false, 300, '', false, false, 1, false, false, false);

       // -------------------------------------------------------------------

       //Close and output PDF document
       $pdf->Output(AROOT . 'tmp/' . 'example_009.pdf', 'FD');

       //============================================================+
       // END OF FILE
       //============================================================+
    }
}
