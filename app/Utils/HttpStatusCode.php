<?php
/**
 * Created by PhpStorm.
 * User: wang
 * Date: 2018/4/13
 * Time: 上午2:01
 */

/**
 * 服务器已经接收到请求头，并且客户端应继续发送请求主体（在需要发送身体的请求的情况下：例如，POST请求），
 * 或者如果请求已经完成，忽略这个响应。
 * */
define('HTTP_STATUS_CONTINUE', 100);

/**
 * 服务器已经理解了客户端的请求，并将通过Upgrade消息头通知客户端采用不同的协议来完成这个请求。在发送完这
 * 个响应最后的空行后，服务器将会切换到在Upgrade消息头中定义的那些协议。
 * */
define('HTTP_STATUS_SWITCHING_PROTOCOLS', 101);

/**
 * WebDAV请求可能包含许多涉及文件操作的子请求，需要很长时间才能完成请求。该代码表示​​服务器已经收到并正在处理
 * 请求，但无响应可用。[6]这样可以防止客户端超时，并假设请求丢失。
 * */
define('HTTP_STATUS_PROCESSING', 102);

/**
 * 请求已成功，请求所希望的响应头或数据体将随此响应返回。实际的响应将取决于所使用的请求方法。在GET请求中，
 * 响应将包含与请求的资源相对应的实体。在POST请求中，响应将包含描述或操作结果的实体。
 * */
define('HTTP_STATUS_OK', 200);

/**
 * 请求已经被实现，而且有一个新的资源已经依据请求的需要而创建，且其URI已经随Location头信息返回。假如需要的
 * 资源无法及时创建的话，应当返回'202 Accepted'。
 * */
define('HTTP_STATUS_CREATED', 201);

/**
 *  请求已经被实现，而且有一个新的资源已经依据请求的需要而创建，且其URI已经随Location头信息返回。假如需要的
 * 资源无法及时创建的话，应当返回'202 Accepted'。[8]
 * */
define('HTTP_STATUS_ACCEPTED', 202);

define('HTTP_STATUS_NON_AUTHOR_INFORM', 203);

define('HTTP_STATUS_NO_CONTENT', 204);

define('HTTP_STATUS_RESET_CONTENT', 205);

define('HTTP_STATUS_PARTIAL_CONTENT', 206);

define('HTTP_STATUS_MULTI_STATUS', 207);

define('HTTP_STATUS_ALREADY_REPORTED', 208);

define('HTTP_STATUS_IM_USED', 226);

define('HTTP_STATUS_MULTIPLE_CHOICES', 300);

define('HTTP_STATUS_MOVED_PERMANENTLY', 301);

define('HTTP_STATUS_FOUND', 302);

define('HTTP_STATUS_SEE_OTHER', 303);

define('HTTP_STATUS_NOT_MODIFIED', 304);

define('HTTP_STATUS_USE_PROXY', 305);

define('HTTP_STATUS_SWITCH_PROXY', 306);

define('HTTP_STATUS_TEMPORARY_REDIRECT', 307);

define('HTTP_STATUS_PERMANENT_REDIRECT', 308);

define('HTTP_STATUS_BAD_REQUEST', 400);

define('HTTP_STATUS_UNAUTHORIZED', 401);

define('HTTP_STATUS_PAYMENT_REQUIRED', 402);

define('HTTP_STATUS_FORBIDDEN', 403);

define('HTTP_STATUS_NOT_FOUND', 404);

define('HTTP_STATUS_METHOD_NOT_ALLOWED', 405);

define('HTTP_STATUS_NOT_ACCEPTABLE', 406);

define('HTTP_STATUS_PROXY_AUTHENTICATION_REQUIRED', 407);

define('HTTP_STATUS_REQUEST_TIMEOUT', 408);

define('HTTP_STATUS_CONFLICT', 409);

define('HTTP_STATUS_GONE', 410);

define('HTTP_STATUS_LENGTH_REQUIRED', 411);

define('HTTP_STATUS_PRECONDITION_FAILED', 412);

define('HTTP_STATUS_REQUEST_ENTITY_TOO_LARGE', 413);

define('HTTP_STATUS_REQUEST_URI_TOO_LONG', 414);

define('HTTP_STATUS_UNSUPPORTED_MEDIA_TYPE', 415);

define('HTTP_STATUS_REQUESTED_RANGE_NOT_SATISFIABLE', 416);

define('HTTP_STATUS_EXPECTATION_FAILED', 417);

define('HTTP_STATUS_TEAPOT', 418);

define('HTTP_STATUS_ENHANCE', 420);

define('HTTP_STATUS_MISDIRECTED_REQUEST', 421);

define('HTTP_STATUS_UNPROCESSABLE_ENTITY', 422);

define('HTTP_STATUS_LOCKED', 423);

define('HTTP_STATUS_FAILED_DEPENDENCY', 424);

define('HTTP_STATUS_UNORDERED_COLLECTION', 425);

define('HTTP_STATUS_UPGRADE_REQUIRED', 426);

define('HTTP_STATUS_PRECONDITION_REQUIRED', 428);

define('HTTP_STATUS_TOO_MANY_REQUESTS', 429);

define('HTTP_STATUS_REQUEST_HEADER_FIELDS_TOO_LARGE', 431);

define('HTTP_STATUS_NO_RESPONSE', 444);

define('HTTP_STATUS_BLOCK_BY_WINDOWS_PARENTAL_CONTROLS', 450);

define('HTTP_STATUS_UNAVAILABLE_FOR_LEGAL_REASONS', 451);

define('HTTP_STATUS_REQUEST_HEADER_TOO_LARGE', 494);

define('HTTP_STATUS_INTERNAL_SERVER_ERROR', 500);

define('HTTP_STATUS_NOT_IMPLEMENTED', 501);

define('HTTP_STATUS_BAD_GATEWAY', 502);

define('HTTP_STATUS_SERVICE_UNAVAILABLE', 503);

define('HTTP_STATUS_GATEWAY_TIMEOUT', 504);

define('HTTP_STATUS_VERSION_NOT_SUPPORTED', 505);

define('HTTP_STATUS_VARIANT_ALSO_NEGOTIATES', 506);

define('HTTP_STATUS_INSUFFICIENT_STORAGE', 507);

define('HTTP_STATUS_LOOP_DETECTED', 508);

define('HTTP_STATUS_NOT_EXTENDED', 510);

define('HTTP_STATUS_NETWORK_AUTHENTICATION_REQUIRED', 511);

//错误编码

define('AUTH_REGISTER_FAIL', 10001);

define('AUTH_LOGOUT_FAIL', 10002);

define('AUTH_NOT_LOGIN', 10003);

define('AUTH_TOKEN_EXPIRES', 10004);

define('HTTP_REQUEST_VALIDATE_ERROR', 20001);

define('PAYMENT_UNIFY_ERROR', 30001);

define('USER_CODE_ERROR',4001);