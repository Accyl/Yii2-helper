# Yii2-helper
对yii2框架基础的helpers进行了一些扩展

## ArrayHelper
- decode: 提供了对数组的解析、过滤操作
- extract: 支持对数组提取指定的列，可支持多维数组结构
- except: 支持移除数组中的指定的列，可支持多维数组结构
- trim: 对数组元素进行过滤操作，支持自定义函数

## StringHelper
- getPassword: 返回密码的32位md5格式
- xssClean: 过滤xss攻击代码
- generateRandomString: 生成一个随机字符串，支持前缀、定长、设置随机因子
- generateSimpleRandomString: 生成一个简单的随机字符串，随机长度不能超过62位，支持前缀
- generateUniqueId: 生成一个唯一ID，固定32位长度
- hideIdentity: 对手机号码和邮箱进行加密操作

## QueryHelper
- parseCondition: 根据请求字符串的内容解析出来AR的where条件
- parseOrder: 根据请求字符串的内容解析出来AR的order条件
- parseQueryPage: 获取当前请求的页数
- parseQuerySize: 获取当前请求的数据列表长度
- parseOffset: 获取当前查询的偏移量
- getTotalPages: 计算总页数
- getInformation: 拼装请求的信息数组

## FileHelper
- getFileTimes: 获取文件的三种时间
- getExtensionByMimeType: 通过文件的mimeType获取文件的后缀名
- saveUploadFileByName: 通过上传的文件名称保存文件
- saveUploadFilesByName: 通过上传的文件名称保存所有文件
- remove: 移除文件或文件夹
- getRootPath: 获取web根目录
- getImageUrl: 转换图片的路径地址
- getUploadFileRootPath: 获取配置的上传文件的根目录