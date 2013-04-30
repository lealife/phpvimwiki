# phpvimwiki


之前该项目在google code上, 现在迁移到github. 算是自娱自乐吧.

我是一名vimwiki的爱好者, 因为喜欢vim, 因为vimwiki的简便让我使用vimwiki记录下我生活的学习, 我做了什么, 我写了什么代码, 我遇到了什么困难, 我怎么去分析解决....这一切都离不开vimwiki.

但有时我并不想将我的记录存在我的本本里, 我也想让别人来分享我的生活记录, 怎么办?

将vimwiki生成好了html上传到网上? 那么总是要本地生成好html之后再上传(麻烦), 而且怎么看也不像一个博客, 虽然可以定义header, footer, css, 但是总觉得不能满足自己的需求.

或者使用另一款在线的wiki程序写wiki? 虽然wiki的语法差不多, 可终就有差异, 而且还要将自己原来的wiki复制过去, 麻烦!

于是我就快速地做了一个phpvimwiki的原型来解决上述问题啦, 用了两天, 想到就做, 自己喜欢就OK了, 当然也期待有其它人喜欢!

## phpvimwiki如何使用

请把你的wiki放在wiki目录下, 然后配置config.php. 你需要写一个quick view的索引wiki. 参照/wiki/php/quick_view.wiki.

## phpvimwiki目录结构
<pre>
index.php 入口文件
config.php 配置文件
include/
    phpvimwiki.php vimwiki语法解析类(主要, 你也可以单独把这个类放在自己写的应用中.)
    quickview.php 内容提要类, 继承自phpvimwiki
wiki/ 你的wiki目录
public/ 
    css/ 
    js/
    images/ 你所写的wiki引用的内部图片放在这里.
layout/ 布局, 你可以修改成你喜欢的.
view/ 视图
page/ 控制器
</pre>
