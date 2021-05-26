<?php
namespace App\Repositories;

// images表 model
use App\Models\Image;
// users表 model
use App\Models\User\User;
// 檔案上傳方法
use Illuminate\Support\Facades\Storage;

class ImageRepository
{
    // Image Model 指定變數
    protected $image;
    // User Model 指定變數
    protected $user;


    /*
    * 將需要使用的Model通過建構函式例項化
    */
    public function __construct(Image $image, User $user)
    {
        $this->image = $image;
        $this->user = $user;
    }
    /**
     * 新增圖片
     * @param object $data 新增圖片資料
     * @param object $imageOrmData 關聯表資料
     */
    public function create($data, $imageOrmData)
    {
        $disk = Storage::disk("gcs");
        // 新增 圖檔至 google cloud storage
        $path = $disk->putFile("operator/".$data["operator_id"]."/".$data["imgType"], $data["imgFile"], "public");
        $image = new $this->image;
        $image->imageable()->associate($imageOrmData);
        $image->fill($data);
        $image->img_path = $path;
        $image->save();
        return $image->fresh();
    }
    /**
     * 更新圖片
     * @param object $data 更新圖片資料
     * @param object $imageOrmData 關聯表資料 (如果值為null 代表不更新關聯的表資料)
     */
    public function update($data, $imageOrmData = null)
    {
        $image = $this->image->find($data["imageId"]);
        $disk = Storage::disk("gcs");
        // 新增 圖檔至 google cloud storage
        $path = $disk->putFile("operator/".$data["operator_id"]."/".$data["imgType"], $data["imgFile"], "public");
        if ($imageOrmData !== null) {
            $image->imageable()->associate($imageOrmData);
        }
        $image->fill($data);
        $image->img_path = $path;
        $image->save();
        return $image->fresh();
    }

    /**
     * 自定義上傳圖片
     * @param string $folderPath 資料夾路徑
     * @param $imgFile 圖片檔案格式
     */
    public function customUplod($folderPath, $imgFile)
    {
        $disk = Storage::disk("gcs");
        $path = $disk->putFile($folderPath, $imgFile, "public");
        return $path;
    }

    /**
     * 刪除圖片
     * @param string $imgPath 圖片路徑
     * @param boolean $deleteData 判斷是否刪除資料庫
     * @param string $imgId 需刪除的id
     */
    public function delete($imgPath, $deleteData = false, $imgId = null)
    {
        $disk = Storage::disk("gcs");
        $disk->delete($imgPath);
        if ($deleteData) {
            $this->image->destroy($imgId);
            return "圖檔已刪除";
        }
        return "檔案已從雲端刪除";
    }
}
