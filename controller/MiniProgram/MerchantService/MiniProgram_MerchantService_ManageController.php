<?php
/**
 * Created by PhpStorm.
 * User: zhangjun
 * Date: 21/11/2018
 * Time: 12:37 PM
 */


class MiniProgram_MerchantService_ManageController extends MiniProgram_BaseController
{

    private $gifMiniProgramId = 107;
    private $title = "商户客服小程序";
    private $pageSize = 200;

    public function getMiniProgramId()
    {
        return $this->gifMiniProgramId;
    }

    public function requestException($ex)
    {
        $this->showPermissionPage();
    }

    public function preRequest()
    {
    }

    public function doRequest()
    {
        header('Access-Control-Allow-Origin: *');
        $method = strtolower($_SERVER['REQUEST_METHOD']);
        $tag = __CLASS__ . "-" . __FUNCTION__;
        $params['lang'] = $this->language;
        $page = isset($_GET['page'] ) ?$_GET['page'] : 1;
        $offset = ($page-1)*$this->pageSize;

        if($method == 'get') {
            $operation = isset($_GET['operation']) ? $_GET['operation'] : "";
            switch ($operation) {
                case "see":
                    $params['services'] = $this->getServiceLists($offset);
                    echo $this->display("miniProgram_merchantService_manageService", $params);
                    break;
                case "add":
                    echo $this->display("miniProgram_merchantService_searchUser", $params);
                    break;
                default:
                    echo $this->display("miniProgram_merchantService_manage", $params);
            }

        } else {
            $operation = isset($_POST['operation']) ? $_POST['operation'] : "";
            switch ($operation) {
                case "delete":
                    $userId = $_POST['userId'];
                    echo $this->deleteMerchantService($userId);
                    break;
                case "search":
                    $searchValue = $_POST['searchValue'];
                    echo $this->getSearchUserInfo($searchValue, $page);
                    break;
                case "add":
                    $userId = $_POST['userId'];
                    echo $this->addMerchantService($userId);
                    break;
            }
        }
    }

    protected function getServiceLists($offset)
    {
        $userInfos = [];
        $results = $this->ctx->SiteMerchantServiceTable->getMerchantServiceLists($offset, $this->pageSize);
        if($results) {
            $userIds  = array_column($results, "userId");
            $userInfos = $this->ctx->Manual_User->getProfiles($this->userId, $userIds);
        }
        return $userInfos;
    }

    protected function deleteMerchantService($userId)
    {
       $flag =  $this->ctx->SiteMerchantServiceTable->delInfoByUserId($userId);
       if($flag) {
           return json_encode(['errCode' => 'success']);
       }
       return json_encode(['errCode' => 'fail']);
    }

    protected function addMerchantService($userId)
    {
        $user = $this->ctx->SiteMerchantServiceTable->getMerchantServiceByUserId($userId);
        if($user) {
            return json_encode(['errCode' => 'success']);
        }
        $userInfo = [
            'userId' => $userId,
            'serviceTime' => ZalyHelper::getMsectime(),
        ];
        $flag =  $this->ctx->SiteMerchantServiceTable->insertMerchantServiceData($userInfo);
        if($flag) {
            return json_encode(['errCode' => 'success']);
        }
        return json_encode(['errCode' => 'fail']);
    }


    protected function getSearchUserInfo($nickname, $page)
    {
        $userInfo = $this->ctx->Manual_User->search($this->userId, $nickname, $page);
        $userIds = array_column($userInfo, 'userId');

        $users = $this->ctx->SiteMerchantServiceTable->getMerchantServiceByUserIds($userIds);
        $serviceUserId = array_column($users, "userId");

        foreach($userInfo as $key => $user) {
            if(in_array($user['userId'], $serviceUserId)) {
                $user['isService'] = 1;
                $userInfo[$key] = $user;
            }
        }

        return  json_encode(['users' => $userInfo, 'errCode' => 'success']);
    }
}