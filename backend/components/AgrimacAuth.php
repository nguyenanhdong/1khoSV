<?php

namespace backend\components;

use Yii;
use backend\components\AgrimacData as D;
use backend\components\AgrimacRepo as R;

/** Vai trò AgriMac của người đang đăng nhập (dùng chung cho trang AgriMac và các trang sàn 1kho). */
class AgrimacAuth
{
    const SESSION_ROLE = 'agrimac_role';

    public static function isSuperAdmin()
    {
        return (int)(Yii::$app->user->identity->is_admin ?? 0) === 1;
    }

    /** Vai trò thật từ RBAC (null nếu chưa được gán). */
    public static function realRole()
    {
        return Yii::$app->user->isGuest ? null : R::roleOfUser(Yii::$app->user->id);
    }

    /** Vai trò đang hiển thị: super admin được chọn "xem theo vai trò" (?role=...). */
    public static function role()
    {
        $role = self::realRole();
        if ($role === 'admin' && self::isSuperAdmin()) {
            $session = Yii::$app->session;
            $requested = Yii::$app->request->get('role');
            if ($requested !== null && isset(D::ROLES[$requested])) {
                $session->set(self::SESSION_ROLE, $requested);
            }
            $demo = $session->get(self::SESSION_ROLE, 'admin');
            return isset(D::ROLES[$demo]) ? $demo : 'admin';
        }
        return $role;
    }

    /**
     * Sale chỉ làm việc với khách tiềm năng (CRM) do mình phụ trách: trả về id nhân viên cần lọc, null = xem tất cả.
     * Super admin đang "xem theo vai trò" Sale không bị lọc (họ không phụ trách lead nào).
     */
    public static function leadSaleScope()
    {
        return self::role() === 'sale' && self::realRole() === 'sale' ? (int)Yii::$app->user->id : null;
    }

    /** Các trang sàn 1kho chỉ dành cho Admin (vai trò thật). */
    public static function canUseLegacy()
    {
        return self::realRole() === 'admin';
    }
}
