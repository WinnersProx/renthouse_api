<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Products Model
 *
 * @method \App\Model\Entity\Product get($primaryKey, $options = [])
 * @method \App\Model\Entity\Product newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Product[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Product|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Product|bool saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Product patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Product[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Product findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ProductsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('products');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');
        $this->hasMany('ProductPictures')->setForeignKey('r_product');
        $this->belongsTo('Categories')->setForeignKey('category')->setProperty('pcategory');
        $this->addBehavior('Timestamp');
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id')
            ->allowEmpty('id', 'create');

        $validator
            ->integer('category')
            ->requirePresence('category', 'create')
            ->notEmpty('category');

        $validator
            ->scalar('product_description')
            ->maxLength('product_description', 255)
            ->requirePresence('product_description', 'create')
            ->notEmpty('product_description');

        $validator
            ->integer('supplier')
            ->requirePresence('supplier', 'create')
            ->notEmpty('supplier');

        $validator
            ->scalar('status')
            ->allowEmpty('status');

        $validator
            ->scalar('motif')
            ->allowEmpty('motif');

        return $validator;
    }
    public function lastUserProduct($user){
        return $this->find()->where(['supplier' => $user])->last();
    }
    public function uploadImages($product_images, $userId){
        $envdir = substr(env('SCRIPT_FILENAME'),0, strrpos(env('SCRIPT_FILENAME'), '/'));
        foreach ($product_images as $picture) {
            // associate a picture to a product post
            // upload staff go here
            $productId = $this->lastUserProduct($userId)->id;
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
                        'picture_url' => $targetFolder. "/".$randomFileName,
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
