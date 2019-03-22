<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event;
/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 *
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */

class ProductsController extends AppController
{

    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
     */
    // var $components = ['CustomUpload'];
    public function initialize(){
        parent::initialize();
        $this->Auth->allow(['list', 'show']);
        $this->loadComponent('RequestHandler');

    }
    public function index()
    {
       $products = $this->Products->find('all');
        $this->set([
            'products' => $products,
            '_serialize' => ['products']
        ]);
    }
    // login users with json web token authentication
    
    /**
     * View method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $product = $this->Products->get($id, [
            'contain' => []
        ]);

        $this->set([
            'product' => $product,
            '_serialize' => ['product']
        ]);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    

    /**
     * Edit method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $product = $this->Products->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $product = $this->Products->patchEntity($product, $this->request->getData());
            if ($this->Products->save($product)) {
                $success = 'The product has been added';
            }
        }
        $this->set([
            'product' => $product,
            'success' => $success,
            '_serialize' => ['product', 'success']
        ]);
    }
    /**
     * addProduct method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function addProduct()
    {
        // debug($this->Products->lastInsertId());
        // die();
        $product = $this->Products->newEntity();
        $message = "Please try again , An error occured";
        if($this->request->is('post')){
            $datas = array_merge($this->request->getData(), ['supplier' => $this->Auth->user('id')]);
            $product = $this->Products->patchEntity($product, $datas);
            $status = "ERROR";
            $this->loadModel('Users');
            if($this->Products->save($product)){
                // make the current user as a supplier if he's not
                if(count($datas['product_images'])){
                    // upload images using associations
                    $this->Products->uploadImages($datas['product_images'], $this->Auth->user('id'));
                }
                $this->Users->supplier($this->Auth->user('id'));
                $message = "Product added successfully";
            }
            else{
                $message = "An error occured , didn't save the product";
            }
        }
        $this->set([
            'message' => $message,
            '_serialize' => ['message']
        ]);
    }

    // returns all of the user products as a supplier
    public function getUserProducts()
    {
        $products = $this->Products->find()->contain(['Categories'])->where(['supplier' => $this->Auth->user('id')]);
        $status = null;
        if($products){
            $status = "OK";
        }
        $this->set([
            'products' => $products,
            '_serialize' => ['products']
        ]);
    }
    public function getProducts()
    {
        $products = $this->Products->find()->contain(['Categories', 'ProductPictures']);
        $this->set([
            'products' => $products,
            '_serialize' => ['products']
        ]);
    }
    /**
     * Delete method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id);
        if ($this->Users->delete($user)) {
            $this->Flash->success(__('The user has been deleted.'));
        } else {
            $this->Flash->error(__('The user could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
