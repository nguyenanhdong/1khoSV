<?php

namespace backend\components;

/**
 * Nén ảnh upload về JPEG không vượt quá $maxBytes (mặc định 2MB) để tiết kiệm dung lượng server.
 * Xoay đúng chiều theo EXIF (ảnh chụp điện thoại), thu nhỏ cạnh dài tối đa $maxSide, giảm chất lượng dần.
 */
class ImageCompressor
{
    /**
     * @return string|null nội dung JPEG đã nén, null nếu không đọc được ảnh
     */
    public static function toJpeg($path, $maxBytes = 2097152, $maxSide = 2000)
    {
        $info = @getimagesize($path);
        if ($info === false) {
            return null;
        }
        switch ($info[2]) {
            case IMAGETYPE_JPEG:
                $img = @imagecreatefromjpeg($path);
                break;
            case IMAGETYPE_PNG:
                $img = @imagecreatefrompng($path);
                break;
            case IMAGETYPE_WEBP:
                $img = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false;
                break;
            default:
                $img = false;
        }
        if (!$img) {
            return null;
        }
        if ($info[2] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
            $exif = @exif_read_data($path);
            $angle = [3 => 180, 6 => -90, 8 => 90][(int)($exif['Orientation'] ?? 1)] ?? 0;
            if ($angle) {
                $rotated = imagerotate($img, $angle, 0);
                if ($rotated) {
                    imagedestroy($img);
                    $img = $rotated;
                }
            }
        }

        $side = $maxSide;
        for ($round = 0; $round < 6; $round++) {
            $resized = self::fit($img, $side);
            foreach ([85, 75, 65, 55] as $quality) {
                ob_start();
                imagejpeg($resized, null, $quality);
                $data = ob_get_clean();
                if (strlen($data) <= $maxBytes) {
                    if ($resized !== $img) {
                        imagedestroy($resized);
                    }
                    imagedestroy($img);
                    return $data;
                }
            }
            if ($resized !== $img) {
                imagedestroy($resized);
            }
            $side = (int)round($side * 0.75);
        }
        imagedestroy($img);
        return $data;
    }

    /** Thu nhỏ để cạnh dài ≤ $side (nền trắng cho ảnh PNG trong suốt) */
    private static function fit($img, $side)
    {
        $w = imagesx($img);
        $h = imagesy($img);
        $scale = min(1, $side / max($w, $h));
        $nw = max(1, (int)round($w * $scale));
        $nh = max(1, (int)round($h * $scale));
        $out = imagecreatetruecolor($nw, $nh);
        imagefill($out, 0, 0, imagecolorallocate($out, 255, 255, 255));
        imagecopyresampled($out, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
        return $out;
    }
}
