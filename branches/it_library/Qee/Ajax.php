<?php
/////////////////////////////////////////////////////////////////////////////
// 这个文件是 QeePHP 项目的一部分
//
// Copyright (c) 2007 - 2008 QeePHP.org (www.qeephp.org)
//
// 要查看完整的版权信息和许可信息，请查看源代码中附带的 COPYRIGHT 文件，
// 或者访问 http://www.qeephp.org/ 获得详细信息。
/////////////////////////////////////////////////////////////////////////////

/**
 * QeePHP 用简单、具有一致性的模型来实现 Ajax 操作。
 *
 * 当初始页面在浏览器中显示出来后，用户的操作会触发一个 Ajax 操作：
 * 1、一个 Ajax 触发器函数（例如点击某个按钮或连接、以及修改了输入框的内容）被调用
 * （由 QeePHP 自动生成的 JavaScript 代码）；
 * 2、关联到这个触发器的数据会以表单（或 URL 参数）的形式传递到服务端的控制器方法；
 * 3、控制器方法（开发者编写的 PHP 代码）通过 $_POST 或 $_GET 获得 Ajax 请求提交的数据；
 * 4、控制器方法进行操作后，返回 HTML 代码片段、JSON 字符串、XML 文档或任意文本；
 * 5、触发器的后续函数（由 QeePHP 自动生成的 JavaScript 代码）将控制器方法返回的结果更新到页面上，
 * 或调用指定的 JavaScript 函数。
 *
 * 要在应用程序中使用 QeePHP 提供的 Ajax 支持，必须做一些准备工作。
 *
 * 首先将 Qee/Ajax 目录中的 jquery.js（已经集成官方 form 插件）文件复制到应用程序可以被浏览器访问到的目录中，
 * 例如 scripts 目录。
 *
 * 接下来，在要使用 Ajax 支持页面的 <head> 和 </head> 标签之间加上：
 *
 * <script language="JavaScript" type="text/javascript" src="scripts/jquery.js"></script>
 * <?php $ajax->dumpJs(); ? >
 *
 * 上述两行代码确保 Ajax 支持需要的 JavaScript 脚本被载入。
 *
 * 此处的 $ajax 对象是 Qee_Ajax 类的一个实例。通过 Qee::initAjax() 获得。
 *
 * @copyright Copyright (c) 2007 - 2008 QeePHP.org (www.qeephp.org)
 * @author 起源科技(www.qeeyuan.com)
 * @package Core
 * @version $Id$
 */

/**
 * Ajax 类提供了大部分 Ajax 操作
 *
 * @package Core
 * @author 起源科技(www.qeeyuan.com)
 * @version 1.0
 */
class Qee_Ajax
{
    /**
     * 已经注册的事件
     *
     * @var array
     */
    protected $_events = array();

    /**
     * 所有 Qee_Ajax 支持的参数的类型
     *
     * @var array
     */
    protected $_paramsType = array(
        'async'         => 'boolean',
        'beforeSend'    => 'function',
        'complete'      => 'function',
        'contentType'   => 'string',
        'params'        => 'pair',
        'data'          => 'object',
        'dataType'      => 'string',
        'error'         => 'function',
        'global'        => 'boolean',
        'ifModified'    => 'boolean',
        'processData'   => 'boolean',
        'success'       => 'function',
        'timeout'       => 'number',
        'type'          => 'string',
        'url'           => 'string',

        'beforeSubmit'  => 'function',
        'semantic'      => 'boolean',
        'clearForm'     => 'boolean',
        'resetForm'     => 'boolean',

        'target'        => 'object',
        'targetValue'   => 'object',
        'clearTarget'   => 'boolean',
    );

    /**
     * 输出 QeePHP 为应用程序动态生成的 JavaScript 脚本
     *
     * 当发现没有载入 JavaScript 脚本库时，该函数会自动输出内容以及一个警告信息。
     *
     * 用法：
     * 在模版中 <?php $ajax->dumpJs(); ? > 即可。
     *
     * @param boolean $return 指示是否返回 js 代码而不是直接输出
     * @param boolean $wrapper 指示是否输出包装脚本的 <script> 标记
     *
     * @return string
     */
    public function dumpJs($return = false, $wrapper = true)
    {
        $out = '';
        if ($wrapper) {
            $out .= "<script language=\"JavaScript\" type=\"text/javascript\">\n";
        }

        // 输出检查 JavaScript 库是否已经正确加载的 JavaScript 代码
        $out .= $this->returnCheckJs();

        // 为已经在服务端注册的事件输出需要的 JavaScript 代码
        $out .= $this->returnEventJs($this->_events);

        if ($wrapper) {
            $out .= "</script>\n";
        }

        if ($return) {
            return $out;
        } else {
            echo $out;
            return null;
        }
    }

    /**
     * 为指定页面对象注册事件响应方法，并返回浏览器端事件响应函数的名字
     *
     * $attribs 可以使用下列属性：
     * - async 指示请求是异步还是同步，默认为异步请求，设置为 false 时使用同步请求
     * - beforeSend 发起请求前要执行的 JavaScript 函数
     * - complete 请求完成后（在 success 或 error 指定的函数执行完成后）要执行的 JavaScript 函数
     * - contentType 请求内容的类型，默认为 "application/x-www-form-urlencoded"
     * - data 要发送到服务器的数据，必须是一个 JavaScript 对象
     * - params 要添加到 URL 的数据，可以是一个数组或一个字符串
     * - dataType 返回数据预期的类型，可以是 html、xml、script、json。默认为根据响应的 MIME 类型来自动判断
     * - error 请求发生错误时要执行的 JavaScript 函数
     * - global 指示这个 ajax 请求是否是全局请求。当 global 为 false 时，将不会引发全局的 ajaxStart/ajaxStop 等处理函数
     * - ifModified 为 true 时，将根据响应头中的 Last-Modified 头信息来判断 ajax 请求是否成功
     * - processData 指示是否将提交的数据转换为查询字符串
     * - success 请求成功时要执行的 javaScript 函数
     * - timeout 设置 ajax 请求的超时时间（秒）
     * - type 请求的类型，post 或 get，默认为 post
     * - url 响应请求的 URL 地址
     *
     * - beforeSubmit （仅用于 submit 事件）用 ajax 提交表单前调用的 JavaScript 函数（在该函数中进行数据验证）
     * - clearForm （仅用于 submit 事件）用 ajax 成功提交表单后，清空表单
     * - resetForm （仅用于 submit 事件）用 ajax 成功提交表单后，重置表单
     *
     * - target 用响应中包含的内容更新指定的页面对象
     * - targetValue 用响应中包含的内容更新指定的页面对象的 value 属性
     * - clearTarget 发起请求后，清除更新目标的内容或 value 属性
     *
     * @param string $control 要绑定的页面对象的 ID
     * @param string $event 要绑定的事件
     * @param string $url 提交 Ajax 请求的目标地址
     * @param array $attribs
     *
     * @return string
     */
    public function registerEvent($control, $event, $url, $attribs = null)
    {
        $control2 = preg_replace('/[^a-z0-9_]+/i', '', $control);
        $functionName = "ajax_{$control2}_on{$event}";
        $this->_events[] = array($control, $event, $url, $attribs, $functionName);
        return $functionName;
    }

    /**
     * 返回检查 jQuery 是否已经加载的 JavaScript 脚本
     *
     * @return string
     */
    public function returnCheckJs()
    {
        $version = Qee_VERSION;
        return <<<EOT
// generated by QeePHP {$version}
if (typeof window.jQuery == "undefined") {
  alert('ERROR: jQuery JavaScript framework failed.');
}


EOT;
    }

    /**
     * 返回页面对象事件的 JavaScript 代码
     *
     * @param array $eventList
     *
     * @return string
     */
    public function returnEventJs(& $eventList)
    {
        $bindEvents = array();
        $out = '';
        foreach ($eventList as $event) {
            $this->_insertAjaxRequest($event, $out, $bindEvents);
            $out .= "\n";
        }
        $bindEvents = implode("\n", $bindEvents);
        return $out . "\n$(function() {\n{$bindEvents}\n});\n";
    }

    /**
     * 生成 ajax 请求需要的 javascript 脚本
     *
     * @param array $event
     * @param string $out
     * @param array $bindEvents
     */
    protected function _insertAjaxRequest(& $eventArr, & $out, & $bindEvents)
    {
        list($control, $event, $url, $attribs, $functionName) = $eventArr;
        $this->_formatAttribs($attribs);
        $bindEvents[] = "    $(\"{$control}\").bind(\"{$event}\", function() { return {$functionName}(); });";

        /**
         * 构造 ajax 请求函数
         */
        $beforeRequest = array();
        $call = $event == 'submit' ? "$(\"{$control}\").ajaxSubmit" : "$.ajax";

        /**
         * 处理 params 属性
         */
        if (isset($attribs['params'])) {
            $params = array();
            parse_str($attribs['params'], $params);
            $params = (array)$params;
            if (!empty($params)) {
                $params = encode_url_args($params, Qee::getAppInf('urlMode'));
                switch (Qee::getAppInf('urlMode')) {
                case URL_PATHINFO:
                case URL_REWRITE:
                    $url .= '/' . $params;
                    break;
                default:
                    if (strpos($url, '?') === false) {
                        $url .= '?';
                    } else {
                        $url .= '&';
                    }
                    $url .= $params;
                }
            }
            unset($attribs['params']);
        }
        $attribs['url'] = '"' . t2js($url) . '"';

        /**
         * 默认使用 post 提交请求
         */
        if (!isset($attribs['type'])) {
            $attribs['type'] = '"post"';
        }

        /**
         * 为 target、targetValue 和 clearTarget 属性生成对应的处理代码
         */
        if (isset($attribs['target']) || isset($attribs['targetValue'])) {
            $targetType = isset($attribs['target']) ? 'html' : 'val';
            $target = ($targetType == 'html') ? $attribs['target'] : $attribs['targetValue'];

            if (isset($attribs['clearTarget']) && $attribs['clearTarget']) {
                $beforeRequest[] = "    {$target}.{$targetType}(\"\");";
            }

            $success = isset($attribs['success']) ? trim($attribs['success']) : '';
            if ($success) {
                $success = preg_replace('/function.+{/i', '{', $success);
                if (substr($success, -1) != ';') { $success .= ';'; }
                $success = "            {$success}\n";
            }

            $attribs['success'] = <<<EOT
function(data) {
            {$target}.{$targetType}(data);
{$success}        }
EOT;

            unset($attribs['target']);
            unset($attribs['targetValue']);
            unset($attribs['clearTarget']);
        }

        $options = '';
        foreach ($attribs as $option => $value) {
            $options .= "        {$option}: {$value},\n";
        }
        $options = substr($options, 0, -2);

        $beforeRequest = implode("\n", $beforeRequest);
        if ($beforeRequest) {
            $beforeRequest = "\n{$beforeRequest}";
        }
        $function = <<<EOT
function {$functionName}()
{{$beforeRequest}
    {$call}({
{$options}
    });

    return false;
}

EOT;

        $out .= $function;
    }

    /**
     * 格式化属性
     *
     * @param array $attribs
     */
    protected function _formatAttribs(& $attribs)
    {
        // 格式化参数
        foreach ($attribs as $option => $value) {
            if (!isset($this->_paramsType[$option])) {
                $type = 'object';
            } else {
                $type = $this->_paramsType[$option];
            }

            switch ($type) {
            case 'raw':
            case 'function':
            case 'number':
                break;
            case 'pair':
                if (is_array($value)) {
                    $value = t2js(encode_url_args($value));
                }
                break;
            case 'boolean':
                $value = $value ? 'true' : 'false';
                break;
            case 'object':
                $value = "$(\"{$value}\")";
                break;
            case 'string':
            default:
                $value = '"' . t2js($value) . '"';
            }

            $attribs[$option] = $value;
        }
    }
}