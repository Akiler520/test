<?php
/**
 * Created by PhpStorm.
 * User: martin
 * Date: 2017/9/18
 * Time: 14:16
 */

set_time_limit(60);
$imagePath = "./temp";
//截屏
echo "test1";
$im = imagegrabscreen();
imagepng($im, "snap1.png");
//抓取IE窗口
$browser = new COM("InternetExplorer.Application");
$handle = $browser->HWND;
$browser->Visible = true;
$im = imagegrabwindow($handle);
$browser->Quit();
imagepng($im, "snap2.png");
echo "test";
$im = imagegrabscreen();
//抓取IE窗口及窗口内容(IE为例)
$browser = new COM("InternetExplorer.Application");
$handle = $browser->HWND;
$browser->Visible = true;
$browser->Navigate("http://www.baidu.com");
while ($browser->Busy) {
    com_message_pump(4000);
}
$im = imagegrabwindow($handle, 0);
$browser->Quit();
imagepng($im, "snap3.png");
// IE全屏模式
$browser = new COM("InternetExplorer.Application");
$handle = $browser->HWND;
$browser->Visible = true;
$browser->FullScreen = true;
$browser->Navigate("http://www.baidu.com");
while ($browser->Busy) {
    com_message_pump(4000);
}
$im = imagegrabwindow($handle, 0);
$browser->Quit();
imagepng($im, "snap4.png");
//生成网站缩略图
$browser = new COM("InternetExplorer.Application");
$handle = $browser->HWND;
$browser->Visible = true;
$browser->Fullscreen = true;
$browser->Navigate("http://www.baidu.com");
while ($browser->Busy) {
    com_message_pump(4000);    //等待4秒
}
$im = imagegrabwindow($handle, 0); //抓取网页图像，需要php5.2.2以上版本的支持
$browser->Quit();
$new_img = imagecreatetruecolor(200,150);
imagecopyresampled($new_img,$im,0,0,0,0,200,150,1024,768);
imagejpeg($new_img , 'snap5.jpg',100);
imagedestroy($new_img);
echo "Done!";
?>