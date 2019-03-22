<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;


class CustomUploadComponent extends Component
{

	public function uploadImages($product_images, $userId){
        $envdir = substr(env('SCRIPT_FILENAME'),0, strrpos(env('SCRIPT_FILENAME'), '/'));
        foreach ($product_images as $picture) {
            // associate a picture to a product post
            // upload staff go here
            $productId = $this->Products->lastUserProduct($userId)->id;
            $fileName = $picture['name'];
            $targetFolder = 'img/posts/'.$productId;
            $fileExt = strrchr($fileName,'.');
            $tmp_name = $picture['tmp_name'];
            $randomFileName = md5(uniqid(rand())).''.$fileExt;
            $filePath = $envdir.'/'.$targetFolder;
            $userFile = $filePath . '/' .$randomFileName;
            if(!file_exists($filePath)){
                mkdir($filePath, 0755, true);
            }
            $allowedExt = ['.png', '.jpeg','.jpg','.PNG','.JPG','.JPEG', '.gif', '.GiF'];
    
            if(in_array($fileExt, $allowedExt)){
                if(move_uploaded_file($tmp_name, $userFile)){
                    // after uploading add in the product_pictures database table
                    $newProductImg = $this->ProductPictures->patchEntity($this->ProductPictures->newEntity(), [
                        'picture_url' => $userFile,
                        'r_product'   => $productId
                    ]);
                    if($this->ProductPictures->save($newProductImg)){
                        // the product pictures were uploaded successfully
                    }
                        
                }
                else{
                    debug($userFile);
                    die("Cannot upload images");
                    $this->Flash->warning(__('The file could not be uploaded'));
                }
            }
            else{
                $this->Flash->error(__('Unable to upload the image'));
            }

        }
    }
}