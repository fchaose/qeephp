<?PHP

//Ĭ��Ӧ�ó�����ļ���·������
if(!defined('APP_ROOT')) define('APP_ROOT', dirname(__FILE__));

return array(

	// cache
	'cached_pages_items'  			=> array
	(
		// ��ʽ: Controller.Action
		'home.index' => 45, // 45 �룬��Ϊ����֮�ã����Ըĳ� 60*60*1 ��1Сʱ��
		'test.list' => 25, // 25 ��
		'test.view' => 15, // 15 ��
	),
	'cached_pages_options'  		=> array(
		'debug' => false,
		'enabled' => true,
		'engine' => 'CacheLite',
		'group' => 'qeetest',
		'cachedir' => APP_ROOT . DS . 'cache',
		'lifetime' => 60*60,
		'fileNameProtection' => FALSE,
		'hashedDirectoryLevel' => 2,
	),

	// displayErrors
	'displayErrors' 		=> TRUE,
	// �ṩ�û���֤�ĳ���
	//'authProvider' 				=> 'My_Auth',
	//������������
    'controllerAccessor'        => 'ctl',
	//����������
    'actionAccessor'            => 'act',
	//Ĭ�ϵĿ�����
	'defaultController'			=> 'home',
	//Ĭ�ϵĶ���
	'defaultAction'				=> 'index',	
	//���������ǰ׺
	'controllerPrefix'			=> 'C_',
	//����ǰ�
	'actionMethodPrefix'		=> 'action',
	// ����URL Mode
    'urlMode'                   => URL_PATHINFO,
	/**
	 * Ӧ�ó���Ҫʹ�õ� url ������
	 */
    'dispatcher'                => 'Qee_Dispatcher',

    'viewEngine' => 'Qee_View_Smarty',
    'viewConfig' => array(
		'smartyDir'    => 'C:\php\PEAR\smarty',	// �˴���Ҫ�޸�Ϊ��ȷ��·��
        'root'         => APP_ROOT . DS,
        'tplrefresh'         => 1,
		'template_dir'      => APP_ROOT . DS . 'templates',
        'compile_dir'       => APP_ROOT . DS . 'templates_c',
        'left_delimiter'    => '<!--{',
        'right_delimiter'   => '}-->',
    ),

	//���ݱ�ǰ׺
	'tablePrefix'		=> 'bbt_',
	
	//���ݿ�����DSN����
	'forums'				=> array(
		'dsn_posts' 	=> 'mysqli://bbsur:PA7SKCVzmQwSR7Ku@cbkdb/bbst?debug=1',
		'dsn_forums' 	=> 'mysqli://bbsur:PA7SKCVzmQwSR7Ku@cbkdb/bbst?debug=1'
	)
	

);
