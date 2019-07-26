<?php //子テーマ用関数

//子テーマ用のビジュアルエディタースタイルを適用
add_editor_style('editor-style.css');

//以下に子テーマ用の関数を書く

//ウィジェットをトップページのリスト表示中間に掲載するか
//if ( !function_exists( 'is_index_middle_widget_visible' ) ):
//function is_index_middle_widget_visible($count){
//  if (
//      //3個目と7個目のときに表示
//      (($count == 3) || ($count == 7)) &&
//      // //トップページリストのみ
//      // is_home() &&
//      // //ページネーションの最終ページでないとき
//      // !is_pagination_last_page() &&
//      //1ページに表示する最大投稿数が6以上の時
//      is_posts_per_page_6_and_over() &&
//      //エントリーカードタイプの一覧のとき
//      //is_entry_card_type_entry_card() &&
//      //タイル表示じゃないとき
//      !is_entry_card_type_tile_card() &&
//      //&&//公開記事が6以上の時
//      (get_all_post_count_in_publish() >= 6)
//  ) {
//    return true;
//  }
//}
//endif;
//インデックスミドル広告の表示条件
add_filter('is_index_middle_ad_visible', function ($is_visible, $count){
  //ここにインデックス広告の表示条件分岐を書く
  // フロントページの場合は1個目の次だけ
  if (is_front_page() && !is_paged() && !(wp_is_mobile() && !is_iPad())) {
    return ($count == 1);
  }
  //それ以外は3個目と7個目のときに表示
  else {
    return (($count == 3) || ($count == 5));
  }

}, 10, 2);

add_filter('cocoon_carousel_args', 'set_popular_postargs');
function set_popular_postargs($args) {

    unset($args['category__in']);    // カテゴリ表示しないので連想配列から指定文字を削除
    unset($args['tag__in']);    // タグ表示しないので連想配列から指定文字を削除
    $args['orderby'] = 'post__in'; // PV順で並べる

    // (関数仕様)
    //function get_access_ranking_records($days = 'all', $limit = 5, $type = 'post', $cat_ids = array(), $exclude_post_ids = array(), $exclude_cat_ids = array()){

    // めんどくさいので引数は決め打ちで済ませる
    $records = get_access_ranking_records('all', $args['posts_per_page'], 'post');

    // 投稿ID格納用の配列を用意
    $post_ids = array();
    // 取得したレコードの投稿IDをすべて上記配列に納める
    foreach ($records as $post) {
        $post_ids[] = $post->ID;    // 配列末尾に追加
    }
    // クエリオプションに投稿ID配列をセット
    $args += array('post__in' => $post_ids);

    //$temp = implode(',', $post_ids);
    //var_dump($temp);

    return $args;
}

////////////////////////////////////////////////////////////
// ヘッダー領域にアナウンスを出すカスタマイズ
////////////////////////////////////////////////////////////
// アナウンス枠エリアウィジェット
if (function_exists('register_sidebar')) {
    register_sidebar(array(
            'name' => 'ヘッダー領域追加コンテンツ',
            'id' => 'add-header-contents',
            'description' => 'ヘッダー領域に追加コンテンツを表示するウィジェットです。',
            'before_widget' => '<div class="add-header-contents wrap">',
            'after_widget' => '</div>',
    ));
}
// ヘッダーロゴの下に出力をする
add_filter('wp_header_logo_after_open', 'add_header_contents');
function add_header_contents() {
    dynamic_sidebar('add-header-contents');
}
////////////////////////////////////////////////////////////
// AMPページでは子テーマのCSS読み込みをamp.cssに限定させる
////////////////////////////////////////////////////////////
add_filter( 'amp_child_css', function(){ return ''; });

////////////////////////////////////////////////////////////
// Rinkerカスタマイズ
////////////////////////////////////////////////////////////
// デフォルトのイメージサイズをLに設定
add_filter('yyi_rinker_default_image_size', 'yyi_rinker_default_image_size');
function yyi_rinker_default_image_size($s) {
    return 'L';
}
// デフォルトのボタンラベル設定
add_filter('yyi_rinker_update_attribute', 'yyi_rinker_custom_shop_labels');
function yyi_rinker_custom_shop_labels($attr) {
	if ( $attr[ 'alabel' ] === '' ) {
		$attr[ 'alabel' ]	= 'Amazonで見る';
	}
	if ( $attr[ 'rlabel' ] === '' ) {
		$attr[ 'rlabel' ]	= '楽天市場で検索';
	}
	if ( $attr[ 'ylabel' ] === '' ) {
		$attr[ 'ylabel' ]	= 'Y!ショッピングで検索';
	}
	if ( $attr[ 'kindle' ] === '' ) {
		$attr[ 'kindle' ]	= 'Kindle版';
	}
	return $attr;
}
// Rinkerクレジット削除
add_filter( 'yyi_rinker_meta_data_update',  'yyi_rinker_delete_credit_html_data', 200 );
function yyi_rinker_delete_credit_html_data( $meta_datas ) {
	$meta_datas[ 'credit' ] = '';
	return $meta_datas;
}

////////////////////////////////////////////////////////////
// Google検索のサムネイル表示対応
////////////////////////////////////////////////////////////
//サムネイルを指定
add_action( 'wp_head', 'add_meta_to_head' );
function add_meta_to_head() {
echo '<meta name="thumbnail" content="' .wp_get_attachment_url( get_post_thumbnail_id() ). '" />';
}

////////////////////////////////////////////////////////////
// iPad判定
////////////////////////////////////////////////////////////
function is_ipad() {
  $is_ipad = (bool) strpos($_SERVER['HTTP_USER_AGENT'],'iPad');
  if ($is_ipad) {
    return true;
  } else {
    return false;
  }
}

////////////////////////////////////////////////////////////
// 月別アーカイブウィジェット
////////////////////////////////////////////////////////////
?>
<?php
class Widget_Archives2 extends WP_Widget {

    function __construct() {
        $widget_ops = array('classname' => 'widget_archives2', 'description' => 'サイトの投稿の年別/月別アーカイブ' );
        parent::__construct('archives2', 'アーカイブ （年別/月別）', $widget_ops);
    }

    function widget( $args, $instance ) {
        extract($args);
        $c = ! empty( $instance['count'] ) ? '1' : '0';
        $title = apply_filters('widget_title', empty($instance['title']) ? __('Archives') : $instance['title'], $instance, $this->id_base);

        echo $before_widget;
        if ( $title )
            echo $before_title . $title . $after_title;

        $this->get_archives(apply_filters('widget_archives2_args', array('show_post_count' => $c)));

        echo $after_widget;
    }

    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $new_instance = wp_parse_args( (array) $new_instance, array( 'title' => '', 'count' => 0) );
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['count'] = $new_instance['count'] ? 1 : 0;

        return $instance;
    }

    function form( $instance ) {
        $instance = wp_parse_args( (array) $instance, array( 'title' => '', 'count' => 0) );
        $title = strip_tags($instance['title']);
        $count = $instance['count'] ? 'checked="checked"' : '';
?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
        <p>
            <input class="checkbox" type="checkbox" <?php echo $count; ?> id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" />
            <label for="<?php echo $this->get_field_id('count'); ?>"><?php _e('Show post counts'); ?></label>
        </p>
<?php
    }

    function get_archives($args = '') {

        $defaults = array(
            'limit' => '',
            'before' => '',
            'after' => '',
            'show_post_count' => false,
            'echo' => 1,
            'order' => 'DESC',
        );

        $r = wp_parse_args( $args, $defaults );
        extract( $r, EXTR_SKIP );

        $arcresults = $this->get_monthly_archives_data($r);

        $output = $this->build_html($r, $arcresults);

        if ( $echo )
            echo $output;
        else
            return $output;
    }

    function get_monthly_archives_data($args) {
        global $wpdb;
        extract( $args, EXTR_SKIP );

        if ( '' != $limit ) {
            $limit = absint($limit);
            $limit = ' LIMIT '.$limit;
        }

        $order = strtoupper( $order );
        if ( $order !== 'ASC' )
            $order = 'DESC';

        //filters
        $where = apply_filters( 'getarchives2_where', "WHERE post_type = 'post' AND post_status = 'publish'", $args );
        $join = apply_filters( 'getarchives2_join', '', $args );

        $query = "SELECT YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, count(ID) as posts FROM $wpdb->posts $join $where GROUP BY YEAR(post_date), MONTH(post_date) ORDER BY post_date $order $limit";
        $key = md5($query);
        $cache = wp_cache_get( 'get_archives2' , 'general');
        if ( !isset( $cache[ $key ] ) ) {
            $arcresults = $wpdb->get_results($query);
            $cache[ $key ] = $arcresults;
            wp_cache_set( 'get_archives2', $cache, 'general' );
        } else {
            $arcresults = $cache[ $key ];
        }

        return $arcresults;
    }

    function build_html($args, $arcresults) {
        extract( $args, EXTR_SKIP );

        if ( !$arcresults )
            return '';

        $cur_year = -1;
        $afterafter = $after;

        $output = '<ul class="yearArchiveList">'; // (1)
        foreach ( (array) $arcresults as $arcresult ) {
            if ( $cur_year != $arcresult->year ) {
                if ( $cur_year > 0 ) {
                    $output .= "</ul>"; // (/3)
                    $output .= "</li>\n"; // (/2)
                }
                $output .= '<li><span class="year">'  . $arcresult->year . "年</span>"; // (2)
                $output .= '<ul class="eachYear">'; // (3)

                $cur_year = $arcresult->year;
            }

            if ( $show_post_count )
                $after = " ({$arcresult->posts}){$afterafter}";

            $output .= '<li class="singleList">' . $this->get_archives_link($arcresult->year, $arcresult->month, $before, $after) . "</li>\n";
        }
        $output .= "</ul>"; // (/3)
        $output .= "</li>\n"; // (/2)
        $output .= "</ul>\n"; // (/1)

        return $output;
    }

    function get_archives_link($year, $month, $before = '', $after = '') {
        global $wp_locale;

        $url = get_month_link($year, $month);
        $url = esc_url($url);

        $text = $wp_locale->get_month($month);
        $text = wptexturize($text);

        $title_text = sprintf(__('%1$s %2$d'), $wp_locale->get_month($month), $year);
        $title_text = esc_attr($title_text);

        $link_html = "$before<a href='$url' title='$title_text'>$text</a>$after";
        $link_html = apply_filters( 'get_archives2_link', $link_html );

        return $link_html;
    }
}

register_widget("Widget_Archives2");

// コメント投稿者のリンクを外部リンクに置き換え
function tblank($text){
	$return = str_replace('<a','<a target="_blank"',$text);
	return $return;
}
add_filter('get_comment_author_link', 'tblank');

// JetPack CDNの画質設定
add_filter( 'jetpack_photon_pre_args', function( $args, $image_url, $scheme ) { if ( empty( $args['quality'] ) ) { $args['quality'] = 100; } return $args; }, 10, 3 );

/////////////////////////////////////////////////////////////////////////////////////////
// 本文抜粋を取得する関数
// ※うまく呼び出せなくなったのでlib/seo.phpの中身をコピーして無理矢理動作させることにした
function get_the_snipet_onchild($content, $length = 70) {
  global $post;

  //抜粋（投稿編集画面）の取得
  $description = $post->post_excerpt;

  //SEO設定のディスクリプション取得
  if (!$description) {
    $description = get_the_page_meta_description($post->ID);
  }

  //SEO設定のディスクリプションがない場合は「All in One SEO Packの値」を取得
  if (!$description) {
    $description = get_the_all_in_one_seo_pack_meta_description();
  }

  //SEO設定のディスクリプションがない場合は「抜粋」を取得
  if (!$description) {
    $description = get_content_excerpt($content, $length);
    $description = str_replace('<', '&lt;', $description);
    $description = str_replace('>', '&gt;', $description);
  }
  return apply_filters( 'get_the_snipet', $description );
}
