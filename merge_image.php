<?php
if (!function_exists('merge_images')) {
    function merge_images($pathdata, $src_path, $new_path = '', $font_path = '', $content = '', $size_num = 0.2)
    {
        $path = explode(',', $pathdata);
        if (empty($path)) {
            return false;
        }
        if (!file_exists($src_path)) {
            return false;
        }
        list($src_w, $src_h, $type_s) = getimagesize($src_path);// 获取水印图片信息
        foreach ($path as $p) {
            list($width, $height, $type) = getimagesize($p);// 获取图片信息

            $bi         = $src_w / $src_h;
            $new_width  = $width * $size_num;//压缩水印是图片的   0.3
            $new_width  = 500;//压缩水印是图片的   0.3
            $new_height = $new_width / $bi;
            $new_height = 500;

            //创建画布
            $image_wp = imagecreatetruecolor($new_width, $new_height);
            $c        = imagecolorallocatealpha($image_wp, 0, 0, 0, 127);//拾取一个完全透明的颜色

            //关闭混合模式，以便透明颜色能覆盖原画布
            imagealphablending($image_wp, false);

            //填充
            imagefill($image_wp, 0, 0, $c);

            //设置保存PNG时保留透明通道信息
            imagesavealpha($image_wp, true);

            $type_source = image_type_to_extension($type_s, false);
            $fun         = 'imagecreatefrom' . $type_source;
            $image       = $fun($src_path);
            imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $src_w, $src_h);

            //新水印图片路径
            $s_path = ROOT_PATH . 'public/sss_s.' . $type_source;

            //调整高度
            switch ($type_s) {
                case 1://GIF
                    imagegif($image_wp, $s_path, 95);
                    break;
                case 2://JPG
                    imagejpeg($image_wp, $s_path, 95);
                    break;
                case 3://PNG
                    imagepng($image_wp, $s_path, 8);
                    break;
            }

            //读取图片元数据
            $dst = imagecreatefromstring(file_get_contents($p));
            $src = imagecreatefromstring(file_get_contents($s_path));

            //获取图片宽高
            list($s_width, $h_height) = getimagesize($s_path);
            $newwidth  = 196;
            $newheight = 374;

            //合并两个图片
            //imagecopy($dst, $src, $newwidth, $newheight, 0, 0, $new_width, $new_height);
            imagecopymerge($dst, $src, $newwidth, $newheight, 0, 0, $new_width, $new_height, 100);

            //添加文字
            if ($font_path != '' && $content != '' && file_exists($font_path)) {
                //3.设置字体颜色和透明度
                $color = imagecolorallocatealpha($dst, 0, 0, 0, 0);
                //4.写入文字 (图片资源，字体大小，旋转角度，坐标x，坐标y，颜色，字体文件，内容)
                imagettftext($dst, 20, 0, 390, 340, $color, $font_path, $content);
            }


            //生成新图片
            $new_src = $p;
            if ($new_path != '') {
                $new_src = $new_path;
            }
            switch ($type) {
                case 1://GIF
                    imagegif($dst, $new_src, 95);
                    break;
                case 2://JPG
                    imagejpeg($dst, $new_src, 95);
                    break;
                case 3://PNG
                    imagepng($dst, $new_src);
                    break;
            }

            //销毁内存数据流
            imagedestroy($image);

            //销毁内存数据流
            imagedestroy($dst);

            //销毁内存数据流
            imagedestroy($src);

            //删除更改图片高度的文件
            @unlink($s_path);


        }
        return true;
    }
}