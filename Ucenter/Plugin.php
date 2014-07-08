<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * Ucenter 集成插件
 * 
 * @package Ucenter
 * @author BinotaLiu
 * @version 1.0.1
 * @link http://binota.org/
 */
class UCenter_Plugin implements Typecho_Plugin_Interface
{

    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        Typecho_Plugin::factory('Widget_User')->login = array('Ucenter_Plugin', 'render');
    }
    
    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate(){}
    
    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form) {}
    
    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}
    
    /**
     * 插件实现方法
     * 
     * @access public
     * @return void
     */
    public static function render($name, $password, $temporarily, $expir)
    {
        $db = Typecho_Db::get(); //載入資料庫

        $ucReturn = self::uc_login($name, $password);

        $user = $db->fetchRow($db->select()
         ->from('table.users')
         ->where((strpos($name, '@') ? 'mail' : 'name') . ' = ?', $name)
         ->limit(1));

        if(!empty($ucReturn) && empty($user)) {
            $uid = self::add_user($name, $password, $ucReturn[3]);
        } elseif(!empty($user)) {
            $uid = $user['uid'];
        } else {
            return FALSE;
        }

        //生成 Auth Code
        $authCode = function_exists('openssl_random_pseudo_bytes') ?
            bin2hex(openssl_random_pseudo_bytes(16)) : sha1(Typecho_Common::randString(20));

        Typecho_Cookie::set('__typecho_uid', $uid, $expire);
        Typecho_Cookie::set('__typecho_authCode', Typecho_Common::hash($authCode), $expire);

        //更新最後登入時間及 Auth COde
        $db->query($db
         ->update('table.users')
         ->expression('logged', 'activated')
         ->rows(array('authCode' => $authCode))
         ->where('uid = ?', $uid));

        return TRUE;
    }

    private static function uc_login($name, $password) {
        include __TYPECHO_ROOT_DIR__ . './uc_config.inc.php';
        include __TYPECHO_ROOT_DIR__ . './uc_client/client.php';
        $ucReturn = uc_user_login($name, $password);
        return ($ucReturn[0] > 0) ? $ucReturn : FALSE;
    }

    private static function add_user($name, $password, $mail) {
        $db = Typecho_Db::get(); //載入資料庫

        //密碼加鹽
        $hashedPwd = Typecho_Common::hash($password);
        //Insert 入庫
        $insert = $db->insert('table.users')
        ->rows(array(
                'name' => $name,
            'password' => $hashedPwd,
                'mail' => $mail,
          'screenName' => $name,
               'group' => 'subscriber'));

        $uid = $db->query($insert);

        return $uid;
    }
}
