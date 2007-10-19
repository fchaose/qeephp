<?PHP
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * Auth_Interface
 *
 * �û���֤��ӿ�
 *
 * @author     liut(liutao@it168.com)
 * @version    $Id$
 */



/**
 * Auth_Interface
 *
 */
interface Auth_Interface
{
	/**
	 * �����û����ֱ��
	 * @return int
	 */
	public function getUid();
	/**
	 * �����û���
	 * @return string
	 */
	public function getUsername();
	/**
	 * �����û�Email
	 * @return string
	 */
	public function getEmail();
	/**
	 * �����Ƿ����Ѿ��Ǽǵ��ҷ������û�
	 * @return bool
	 */
	public function isAuthed();
	/**
	 * ��֤��¼
	 * @return bool
	 */
	public function login($username, $password);
	/**
	 * ��֤��¼���ض��򵽵�¼��ַ
	 * @return bool
	 */
	public function verifyLogin($url, $redirect = true);
	/**
	 * �˳�����������Ϣ����Cookie��Session�ȣ�
	 * @return bool
	 */
	public function logout();
}