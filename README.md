# 🗳️ WPDiscuz React Votes API

A lightweight WordPress plugin that exposes a custom REST API endpoint for **wpDiscuz comment likes/dislikes** — built for React Native, Next.js, and other headless frontends.

![License](https://img.shields.io/badge/license-GPL--2.0-blue.svg)
![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-blue?logo=wordpress)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?logo=php)
![wpDiscuz](https://img.shields.io/badge/wpDiscuz-7.0%2B-green)

---

## ✨ Features

- 🔹 REST API route for comment likes/dislikes  
- 🔹 Works seamlessly with **wpDiscuz**’s internal reaction system  
- 🔹 Returns updated like/dislike counts instantly  
- 🔹 Public or protected access (you decide)  
- 🔹 Fully compatible with **React Native** and **headless WordPress**

---

## ⚙️ Requirements

- **WordPress** ≥ 5.8  
- **wpDiscuz** ≥ 7.0  
- **PHP** ≥ 7.4  

---

## 🧩 Installation

1. **Download or clone** this repository into your WordPress plugins folder:

   cd wp-content/plugins
   git clone https://github.com/colorado4sure/wpdiscuz-reactions-api.git

1. Go to your WordPress Admin → **Plugins → Installed Plugins**  
2. Activate **WPDiscuz React Votes API**  
3. Ensure **wpDiscuz** is active and functional


### Option 2 — Upload Zip

1. Compress the plugin folder into a `.zip`  
2. Go to **WordPress Admin → Plugins → Add New → Upload Plugin**  
3. Upload and activate the plugin

---

## 🔌 REST API Endpoints

### 🔹 Get Reaction Counts

Retrieve total likes and dislikes for a specific comment.

**Endpoint**

    GET /wp-json/wpdiscuz/v2/comment/{comment_id}/reactions

**Example**

    curl https://example.com/wp-json/wpdiscuz/v2/comment/42/reactions
  Response
  
    {
      "comment_id": 42,
      "likes": 10,
      "dislikes": 2
    }
    
  🔹 Submit a Reaction (Like / Dislike)
    Add or update a user’s reaction on a comment.

Endpoint

    POST /wp-json/wpdiscuz/v2/comment/{comment_id}/vote
Body

    {
      "type": "like"
    }
  Accepted values for type: "like" or "dislike"

Response

    {
      "success": true,
      "comment_id": 42,
      "likes": 11,
      "dislikes": 2
    }
    
🔐 Authentication
By default, routes are public:

    
    'permission_callback' => '__return_true'
  
  To limit to logged-in users, change the callback:
  
    'permission_callback' => function() {
        return is_user_logged_in();
    };
    
📱 Example React Integration

    import axios from "axios";
    
    const API_URL = "https://example.com/wp-json/wpdiscuz/v2";
    
    export const getReactions = async (commentId) => {
      const res = await axios.get(`${API_URL}/comment/${commentId}/reactions`);
      return res.data;
    };
    
    export const sendReaction = async (commentId, type) => {
      const res = await axios.post(`${API_URL}/comment/${commentId}/vote`, { type });
      return res.data;
    };
    
## 🗃️ Data Structure

This plugin leverages the WordPress **`comment_meta`** table to store reaction data.

| Meta Key             | Description                         |
|----------------------|-------------------------------------|
| `_wpdiscuz_likes`    | 👍 Number of likes for the comment    |
| `_wpdiscuz_dislikes` | 👎 Number of dislikes for the comment |

> 🛠️ You can modify these meta keys if your **wpDiscuz** configuration uses different ones.

📂 Folder Structure

    wpdiscuz-react-votes-api/
    ├── wpdiscuz-react-votes-api.php
    ├── README.md
    └── assets/
        └── icon.png
        
🧑‍💻 Developer Notes
  -Built specifically for headless WordPress setups
  -Mirrors wpDiscuz’s built-in reaction handling for consistency
  -Follows WordPress REST API standards
  -Easily extendable for user-reaction tracking or rate-limiting

🧡 Credits
wpDiscuz — by gVectors Team

Developed by PopNaija LTD

⭐ Like this project? Give it a star on GitHub and share feedback or feature requests through Issues.




