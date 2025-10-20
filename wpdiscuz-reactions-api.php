<?php

/**
 * Plugin Name: WPDiscuz Reactions API
 * Description: REST API endpoints for wpDiscuz comment likes and dislikes — integrates directly with wpDiscuz vote table for consistency.
 * Version: 1.0.0
 * Author: Colorado Akpan
 * License: GPL2+
 */

if (!defined('ABSPATH')) exit;

class WPDiscuz_Reactions_API
{

      private $table;

      public function __construct()
      {
            global $wpdb;
            $this->table = $wpdb->prefix . 'wpdiscuz_votes';

            add_action('rest_api_init', [$this, 'register_routes']);
      }

      public function register_routes()
      {
            register_rest_route('wpdiscuz/v2', '/comment/(?P<id>\d+)/vote', [
                  'methods'             => 'POST',
                  'callback'            => [$this, 'handle_vote'],
                  'permission_callback' => '__return_true',
                  'args' => [
                        'type' => [
                              'required' => true,
                              'validate_callback' => function ($param) {
                                    return in_array($param, ['like', 'dislike'], true);
                              }
                        ],
                  ],
            ]);

            register_rest_route('wpdiscuz/v2', '/comment/(?P<id>\d+)/reactions', [
                  'methods'             => 'GET',
                  'callback'            => [$this, 'get_reactions'],
                  'permission_callback' => '__return_true',
            ]);
      }

      /**
       * Handle like/dislike vote
       */
      public function handle_vote($request)
      {
            global $wpdb;

            $comment_id = intval($request['id']);
            $type = sanitize_text_field($request->get_param('type'));
            $ip = $_SERVER['REMOTE_ADDR'];
            $user_id = get_current_user_id();

            $comment = get_comment($comment_id);
            if (!$comment) {
                  return new WP_Error('invalid_comment', 'Comment not found', ['status' => 404]);
            }

            // Check if user/IP already voted
            $existing = $wpdb->get_row($wpdb->prepare(
                  "SELECT * FROM {$this->table} WHERE comment_id = %d AND (ip_address = %s OR user_id = %d)",
                  $comment_id,
                  $ip,
                  $user_id
            ));

            // Toggle logic: if same type, remove vote; if opposite, update
            $vote_value = ($type === 'like') ? 1 : -1;

            if ($existing) {
                  if (intval($existing->vote) === $vote_value) {
                        // Same vote -> undo (delete)
                        $wpdb->delete($this->table, ['id' => $existing->id]);
                        $action = 'removed';
                  } else {
                        // Opposite vote -> update
                        $wpdb->update($this->table, ['vote' => $vote_value, 'date' => current_time('mysql')], ['id' => $existing->id]);
                        $action = 'switched';
                  }
            } else {
                  // New vote
                  $wpdb->insert($this->table, [
                        'vote' => $vote_value,
                        'user_id' => $user_id,
                        'ip_address' => $ip,
                        'comment_id' => $comment_id,
                        'post_id' => $comment->comment_post_ID,
                        'date' => current_time('mysql'),
                  ]);
                  $action = 'added';
            }

            // Fetch updated totals
            $likes = $wpdb->get_var($wpdb->prepare(
                  "SELECT COUNT(*) FROM {$this->table} WHERE comment_id = %d AND vote = 1",
                  $comment_id
            ));
            $dislikes = $wpdb->get_var($wpdb->prepare(
                  "SELECT COUNT(*) FROM {$this->table} WHERE comment_id = %d AND vote = -1",
                  $comment_id
            ));

            return [
                  'success' => true,
                  'action' => $action,
                  'comment_id' => $comment_id,
                  'likes' => intval($likes),
                  'dislikes' => intval($dislikes),
            ];
      }

      /**
       * Get current like/dislike counts for a comment
       */
      public function get_reactions($request)
      {
            global $wpdb;

            $comment_id = intval($request['id']);
            $comment = get_comment($comment_id);

            if (!$comment) {
                  return new WP_Error('invalid_comment', 'Comment not found', ['status' => 404]);
            }

            $likes = $wpdb->get_var($wpdb->prepare(
                  "SELECT COUNT(*) FROM {$this->table} WHERE comment_id = %d AND vote = 1",
                  $comment_id
            ));
            $dislikes = $wpdb->get_var($wpdb->prepare(
                  "SELECT COUNT(*) FROM {$this->table} WHERE comment_id = %d AND vote = -1",
                  $comment_id
            ));

            return [
                  'comment_id' => $comment_id,
                  'likes' => intval($likes),
                  'dislikes' => intval($dislikes),
            ];
      }
}

new WPDiscuz_Reactions_API();
