<?php
 
namespace app\components;

class Translation {

    const SUPPORTED_LANGUAGES = ['Ar'];

    const TERMS = [
        'new_friend_request_title' => [
            'En' => 'New Friend Request',
            'Ar' => 'طلب صداقة جديد',
        ],
        'new_friend_request_body' => [
            'En' => 'wants to add you as a friend',
            'Ar' => 'يريد اضافتك كصديق',
        ],

        'friend_request_accepted_title' => [
            'En' => 'Friend Request Accepted',
            'Ar' => 'تم قبول طلب صداقة',
        ],
        'friend_request_accepted_body' => [
            'En' => ' accepted your friend request',
            'Ar' => 'قام بقبول طلب الصداقة',
        ],

        'new_media_title' => [
            'En' => 'New Media',
            'Ar' => 'اشارة جديدة فى الوسائط',
        ],
        'new_media_body' => [
            'En' => 'posted an image on',
            'Ar' => 'اضاف صورة على',
        ],

        'new_favorite_title' => [
            'En' => 'New Favorite',
            'Ar' => 'اشارة جديدة فى تقييم',
        ],
        'new_favorite_body' => [
            'En' => 'saved',
            'Ar' => 'قام بحفظ',
        ],

        'new_checkin_title' => [
            'En' => 'New Checkin',
            'Ar' => 'تسجيل وصول جديد',
        ],
        'new_checkin_body' => [
            'En' => 'has checked in to',
            'Ar' => 'قام بتسجيل الوصول إلى',
        ],

        'new_review_title' => [
            'En' => 'New Review',
            'Ar' => 'تقييم جديد',
        ],
        'new_review_body' => [
            'En' => 'has posted a review on',
            'Ar' => 'قام بأضافة تقييم على',
        ],

        'new_review_tag_title' => [
            'En' => 'New Review Tag',
            'Ar' => 'اشارة جديدة فى تقييم',
        ],
        'new_review_tag_body' => [
            'En' => 'has tagged you in review for',
            'Ar' => 'قام بالاشارة لك فى تقييم لك على',
        ],

        'new_image_title' => [
            'En' => 'New Image',
            'Ar' => 'صورة جديدة',
        ],
        'new_video_title' => [
            'En' => 'New Video',
            'Ar' => 'فيديو جديد',
        ],
        'new_menu_title' => [
            'En' => 'New Menu',
            'Ar' => 'قائمة طعام جديدة',
        ],
        'new_product_title' => [
            'En' => 'New Product',
            'Ar' => 'منتج جديد',
        ],
        'new_brochure_title' => [
            'En' => 'New Brochure',
            'Ar' => 'بروشور جديد',
        ],
        'new_image_body' => [
            'En' => 'has posted a image on',
            'Ar' => 'قام بأضافة صورة على',
        ],
        'new_video_body' => [
            'En' => 'has posted a video on',
            'Ar' => 'قام بأضافة فيديو على',
        ],
        'new_menu_body' => [
            'En' => 'has posted a menu on',
            'Ar' => 'قام بأضافة قائمة طعام على',
        ],
        'new_product_body' => [
            'En' => 'has posted a product on',
            'Ar' => 'قام بأضافة منتج على',
        ],
        'new_brochure_body' => [
            'En' => 'has posted a brochure on',
            'Ar' => 'قام بأضافة بروشور على',
        ],

        'new_comment_title' => [
            'En' => 'New Comment',
            'Ar' => 'تعليق جديد',
        ],
        'new_comment_body' => [
            'En' => 'added new comment to your',
            'Ar' => 'قام بالتعليق على',
        ],

        'new_comment_tag_title' => [
            'En' => 'New Comment Tag',
            'Ar' => 'اشارة جديدة فى تعليق',
        ],
        'new_comment_tag_body' => [
            'En' => 'has tagged you in comment',
            'Ar' => 'قام بالاشارة لك فى تعليق',
        ],

        'edit_comment_title' => [
            'En' => 'Edit Comment',
            'Ar' => 'تعديل تعليق',
        ],
        'edit_comment_body' => [
            'En' => 'edited comment to your',
            'Ar' => 'قام بتعديل التعليق على',
        ],
    ];

    public static function get($lang, $text)
    {
        if (!in_array($lang, static::SUPPORTED_LANGUAGES)) {
            $lang = 'En';
        }

        if (preg_match_all('/{(.*?)}/', $text, $matches)) {
            foreach ($matches[1] as $match) {
                $text = str_replace('{' . $match . '}', static::TERMS[$match][$lang], $text);
            }
        }

        return $text;
    }
}
