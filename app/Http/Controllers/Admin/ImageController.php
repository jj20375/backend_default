<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// 導入 image 服務
use App\Services\ImageService;
// 導入 共用方法
use App\Helpers\FuncHelper;
// 導入 表單驗證方法
use Illuminate\Support\Facades\Validator;

class ImageController extends Controller
{
    // imageService Service 指定變數
    protected $imageService;
    // 導入共用方法 指定變數
    protected $funcHelper;
    
    public function __construct(ImageService $imageService, FuncHelper $funcHelper)
    {
        $this->imageService = $imageService;
        $this->funcHelper = $funcHelper;
    }

    // 客製化圖片上傳或者編輯器圖片上傳專用
    public function customUpload(Request $request)
    {
        // 驗證規則
        $rules = [
            "imgFile" => "image|max:1540",
            "folderPath" => "required|string",
        ];
        // 驗證錯誤訊息
        $messages = [
            "imgFile.image" => "請傳入正確的圖片格式",
            "imgFile.max" => "圖片檔案過大",
            "folderPath.required" => "請傳入檔案路徑",
            "folderPath.string" => "檔案路徑為字串",
        ];
        // 判斷是否有傳入圖片檔案
        if (!$request->hasFile('imgFile')) {
            return $this->funcHelper->errorBack("請傳入圖片檔案", 500);
        }
        // 表單驗證
        $checkValidate = Validator::make($request->input(), $rules, $messages);
        // 判斷表單驗證是否通過
        if ($checkValidate->fails()) {
            return $this->funcHelper->errorBack($checkValidate->errors(), 500);
        }
        // 圖片路徑
        $imgPath = $this->imageService->customUplod($request->folderPath, $request->file("imgFile"));
        return $this->funcHelper->successBack($imgPath);
    }
}
