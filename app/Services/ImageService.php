<?php
namespace App\Services;

// 導入 images 資料庫操作方式
use App\Repositories\ImageRepository;
// 導入 共用方法
use App\Helpers\FuncHelper;

class ImageService
{
    // Imgage Repository 指定變數
    protected $imageRepository;
    // 導入共用方法 指定變數
    protected $funcHelper;
    

    /*
    * 將需要使用的Repository通過建構函式例項化
    */
    public function __construct(ImageRepository $imageRepository, FuncHelper $funcHelper)
    {
        $this->imageRepository = $imageRepository;
        $this->funcHelper = $funcHelper;
    }

    /**
     * 自定義上傳圖片
     * @param string $folderPath 資料夾路徑
     * @param $imgFile 圖片檔案格式
     */
    public function customUplod($folderPath, $imgFile)
    {
        $imgPath = $this->imageRepository->customUplod($folderPath, $imgFile);
        return $imgPath;
    }
}
