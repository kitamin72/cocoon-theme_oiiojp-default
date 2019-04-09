<?php //子テーマ用関数

//子テーマ用のビジュアルエディタースタイルを適用
add_editor_style('editor-style.css');

//以下に子テーマ用の関数を書く

//ウィジェットをトップページのリスト表示中間に掲載するか
if ( !function_exists( 'is_index_middle_widget_visible' ) ):
function is_index_middle_widget_visible($count){
  if (
      //3個目と7個目のときに表示
      (($count == 3) || ($count == 7)) &&
      // //トップページリストのみ
      // is_home() &&
      // //ページネーションの最終ページでないとき
      // !is_pagination_last_page() &&
      //1ページに表示する最大投稿数が6以上の時
      is_posts_per_page_6_and_over() &&
      //エントリーカードタイプの一覧のとき
      //is_entry_card_type_entry_card() &&
      //タイル表示じゃないとき
      !is_entry_card_type_tile_card() &&
      //&&//公開記事が6以上の時
      (get_all_post_count_in_publish() >= 6)
  ) {
    return true;
  }
}
endif;
