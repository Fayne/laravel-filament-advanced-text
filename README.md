<p align="center"><img src="https://res.cloudinary.com/fayne/image/upload/v1787643269/fayne_logo.png" width="300"></p>

# Laravel Filament Advanced Text

简体中文 | [English](docs/README.en.md)

增强文本字段，适用于 **PHP 8.4+、Laravel 13、Filament 5**。

## 功能与边界

独立可复用的文本输入，支持前后缀拼接、静态及远程建议、复制、Unicode 长度和输入法组合状态处理。无需安装 CMS、Page 或 Menu。

## 安装

先在宿主 Composer 配置本私有包及所需 `fayne/*` 依赖的软件源，再执行：

```bash
composer require fayne/laravel-filament-advanced-text
```

Laravel 自动发现服务提供者。仅提供字段的包无需注册 Panel 插件；业务包按下面的示例注册。依赖版本以 [composer.json](composer.json) 为准，不要复制开发宿主的私有仓库凭据。

## 使用与配置

```php
use Fayne\AdvancedText\Forms\Components\AdvancedText;

AdvancedText::make('sku')
    ->prefix('SKU-')->suffix('-STD')
    ->prefixConcatenate()->suffixConcatenate()
    ->options(['基础版' => 'basic', '专业版' => 'pro'])
    ->datalist(['suggested-value'])
    ->copyable()->maxLength(40);
```

`maxLength` 只计算可编辑正文，不含前后缀。两个拼接选项默认开启，保存时包含前后缀，编辑已有值时移除已有前后缀，避免反复保存重复拼接。

建议允许自由输入；`options()` 是显示名称到保存值的映射。`remoteOptionsUrl()` 接收接口地址，接口读取防抖后的 `query` 参数并返回名称到标量值的 JSON 对象。同源请求使用当前会话；跨域请求不携带会话，须有 CORS 并在发布后的 `config/filament-advanced-text.php` 中许可准确 URL：

```bash
php artisan vendor:publish --tag=filament-advanced-text-config
```

密钥应保留在服务端代理。`readOnly()` 只控制交互，宿主仍须校验权限并保护不可修改值。

## 测试与上线

在本包目录执行 `composer install`、`composer test`、`composer format`。自动化检查不能代替宿主表单和真实环境验收。

## 合作与贡献

通过 Issue 描述需求或缺陷，提供复现步骤、版本和预期行为；提交合并请求时补充相关验证，并说明尚未验证的环境。请勿提交密钥或业务敏感数据。商业合作及授权请联系 [Fayne Guo](mailto:fayne.guo@gmail.com)。

## 许可证

采用[专有许可证](LICENSE.md)。未经授权不得复制、分发或使用；第三方依赖仍遵循各自许可证。

## 开发与安全

[开发与 CI 约定](CONTRIBUTING.md) · [安全报告](SECURITY.md)。运行 `composer check` 检查语法和格式；有独立套件时同时运行测试。包不提交 `composer.lock`。
