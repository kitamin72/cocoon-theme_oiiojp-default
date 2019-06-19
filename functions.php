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
  //3個目と7個目のときに表示
  return (($count == 3) || ($count == 7));
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
            'before_widget' => '<div class="add-header-contents">',
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

