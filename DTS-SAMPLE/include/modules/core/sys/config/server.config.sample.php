<?php

//������������Ϸ��װʱ��ʼ��������ͨ����Ϸ��̨�޸�
// [EN]	Set below parameters according to your account information provided by your hosting
// [CH] ���±�������ݿռ����ṩ���˺Ų����޸� ��������,����ϵ�������ṩ��

	$server_address = 'http://127.0.0.1/dts'; 			//���ط���������������daemon����Ҫ������б�ܣ�
	
	$dbhost = '127.0.0.1';			// database server
						// ���ݿ������

	$dbuser = 'root';			// database username
						// ���ݿ��û���

	$dbpw = 'root';			// database password
						// ���ݿ�����

	$dbname = 'acdts';			// database name
						// ���ݿ���

	$dbreport = 1;				// send db error report? 1=yes
						// �Ƿ������ݿ���󱨸�? 0=�� 1=��

// [EN] If you have problems logging in Discuz!, then modify the following parameters, else please leave default
// [CH] ����?cookie ���÷�Χ������Ҫ�����Ϸ��¼������,���޸�������������뱣��Ĭ��

	$cookiedomain = ''; 			// cookie domain
						// cookie ������

	$cookiepath = '/';			// cookie path
						// cookie ����·��


// [EN] Special parameters, DO NOT modify these unless you are an expert in Discuz!
// [CH] ���±���Ϊ�ر�ѡ��,һ�������û�б�Ҫ�޸�

	$headercharset = 0;			// force outputing charset header
						// ǿ�������ַ�����ֻ����ʱʹ��

	$onlinehold = 900;			// time span of online recording
						// ���߱���ʱ��,��λ��

	$pconnect = true;				// persistent database connection, 0=off, 1=on
						// ���ݿ�־����� false=�ر�, true=�� mysql֮���ģʽ���ܲ�����

	$gamefounder = 'admin';			// super administrator's UID
						// ��Ϸ��ʼ��UID���൱��Ȩ��10

	$moveut = 8; //set the difference of server time and client time
			//�������ʱ���������ʱ����ʱ��ڴ˴�����

	$moveutmin = 0; //set the difference of server time and client time, by minutes
	//�������ʱ���������ʱ���з��Ӳ�ڴ˴����ģ���λ����

// [EN] !ATTENTION! Do NOT modify following after your board was settle down
// [CH] ��ϷͶ��ʹ�ú����޸ĵı���

	$gtablepre = 'acbra2_';   			// ����ǰ׺, ͬһ���ݿⰲװ�����Ϸ���޸Ĵ˴�
						// table prefix, modify this when you are installingmore than 1 Discuz! in the same database.

	$authkey = 'bra';		//game encrypt key ,the same of plus key
						//��Ϸ������Կ��Ҫ������Կ��ͬ

// [EN] !ATTENTION! Preservation or debugging for developing
// [CH] �����޸����±���,�������򿪷�������!

	$database = 'mysql';			// 'mysql' for MySQL version and 'pgsql' for PostgreSQL version
						// MySQL �汾������'mysql', MySQLi �汾������'mysqli'

	$charset = 'utf-8';			// default character set, 'gbk', 'big5', 'utf-8' are available
						// ��ϷĬ���ַ��� ��ѡ'gbk', 'big5', 'utf-8'

	$dbcharset = 'utf8';			// default database character set, 'gbk', 'big5', 'utf8', 'latin1' and blank are available
						// MySQL �ַ��� ��ѡ'gbk', 'big5', 'utf8', 'latin1', ����Ϊ������Ϸ�ַ����趨

	$tplrefresh = 1;			// auto check validation of templates, 0=off, 1=on
						// ģ���Զ�ˢ�¿��� 0=�ر�, 1=��, �ڲ��޸�ҳ�������¿��Թر�

	$bbsurl = 'http://000.76573.org/';    //the bbs url for the game plus
									//��װ��Ϸ�������̳��ַ

	$gameurl = 'http://127.0.0.1/dts';    // the url of game program files,for the full-window mode
									//��Ϸ������ַ�����ڽ�������

	$homepage = 'http://soul573.com/';      // game homepage
									//�ٷ���վ��ַ

	$title = '�� �� �� �� ɱ';     //game title
							//��Ϸ���� �������ã��ѷ�������ȥtemplate.lang.php���޸�

	$errorinfo = 1;				//�Ƿ���������Ϣ��ʾ��1Ϊ������0Ϊ�رա�������й©��Ϸ��װ·��

	
// ============================================================================

?>
