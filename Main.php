<?php

$library = [
    "Fiction" => [
        "Fantasy" => ["Harry Potter", "The Hobbit"],
        "Mystery" => ["Sherlock Holmes", "Gone Girl"]
    ],
    "Non-Fiction" => [
        "Science" => ["A Brief History of Time", "The Selfish Gene"],
        "Biography" => ["Steve Jobs", "Becoming"]
    ]
];

$bookDetails = [
    "Harry Potter"              => ["author" => "J.K. Rowling",        "year" => 1997, "genre" => "Fantasy", "summary" => "A young wizard discovers his destiny."],
    "The Hobbit"                => ["author" => "J.R.R. Tolkien",      "year" => 1937, "genre" => "Fantasy", "summary" => "A hobbit's adventure to reclaim a kingdom."],
    "Gone Girl"                 => ["author" => "Gillian Flynn",       "year" => 2012, "genre" => "Mystery", "summary" => "A marriage mystery with dark twists."],
    "Sherlock Holmes"           => ["author" => "Arthur Conan Doyle",  "year" => 1892, "genre" => "Mystery", "summary" => "Classic detective stories."],
    "A Brief History of Time"   => ["author" => "Stephen Hawking",     "year" => 1988, "genre" => "Science", "summary" => "Cosmology explained for general readers."],
    "The Selfish Gene"          => ["author" => "Richard Dawkins",     "year" => 1976, "genre" => "Science", "summary" => "Evolutionary biology and gene-centered view."],
    "Steve Jobs"                => ["author" => "Walter Isaacson",     "year" => 2011, "genre" => "Biography", "summary" => "The life of Apple's co-founder."],
    "Becoming"                  => ["author" => "Michelle Obama",      "year" => 2018, "genre" => "Biography", "summary" => "Memoir by the former First Lady."],
];

function getStringParam(string $k): ?string {
    if (!isset($_GET[$k])) return null;
    $v = trim((string)$_GET[$k]);
    return $v === '' ? null : $v;
}
$selectedRaw = getStringParam('book');
$searchRaw   = getStringParam('search');
$selected    = $selectedRaw ? rawurldecode($selectedRaw) : null;
$searchQuery = $searchRaw ? $searchRaw : null;

class Node {
    public string $val;
    public ?Node $left = null;
    public ?Node $right = null;
    public function __construct(string $v) { $this->val = $v; }
}

class BST {
    private ?Node $root = null;
    public function insert(string $v): void { $this->root = $this->ins($this->root, $v); }
    private function ins(?Node $n, string $v): Node {
        if ($n === null) return new Node($v);
        $c = strcasecmp($v, $n->val);
        if ($c < 0) $n->left = $this->ins($n->left, $v);
        elseif ($c > 0) $n->right = $this->ins($n->right, $v);
        return $n;
    }
    public function search(string $v): bool { return $this->s($this->root, $v); }
    private function s(?Node $n, string $v): bool {
        if ($n === null) return false;
        $c = strcasecmp($v, $n->val);
        if ($c === 0) return true;
        return $c < 0 ? $this->s($n->left, $v) : $this->s($n->right, $v);
    }
    public function inorder(callable $cb): void { $this->in($this->root, $cb); }
    private function in(?Node $n, callable $cb): void {
        if ($n === null) return;
        $this->in($n->left, $cb);
        $cb($n->val);
        $this->in($n->right, $cb);
    }
}

$bst = new BST();
foreach (array_keys($bookDetails) as $t) $bst->insert($t);

function renderSidebar(array $tree, ?string $selected) {
    foreach ($tree as $k => $v) {
        if (is_array($v)) {
            echo "<div class='category-section'>";
            echo "<div class='category-header'>" . htmlspecialchars($k, ENT_QUOTES) . "</div>";
            echo "<div class='category-books'>";
            renderSidebar($v, $selected);
            echo "</div></div>";
        } else {
            $title = (string)$v;
            $active = ($selected !== null && strcasecmp($selected, $title) === 0) ? "active" : "";
            $url = "?book=" . rawurlencode($title);
            echo "<a class='book-link $active' href='$url'>";
            echo "<span class='book-icon'>📖</span>";
            echo "<span>" . htmlspecialchars($title, ENT_QUOTES) . "</span>";
            echo "</a>";
        }
    }
}

function renderBookCard(?string $title, array $details) {
    if ($title === null) {
        echo "<div class='empty-state'>";
        echo "<div class='empty-icon'>📚</div>";
        echo "<h2>Your Digital Library</h2>";
        echo "<p>Browse categories on the left or use the search feature to find books</p>";
        echo "</div>";
        return;
    }
    if (!array_key_exists($title, $details)) {
        echo "<div class='error-card'>📭 Book not found in our catalog</div>";
        return;
    }
    $d = $details[$title];
    echo "<div class='book-display'>";
    echo "<div class='book-cover'>";
    echo "<div class='cover-text'>" . htmlspecialchars($title, ENT_QUOTES) . "</div>";
    echo "<div class='cover-shine'></div>";
    echo "</div>";
    echo "<div class='book-info'>";
    echo "<h1>" . htmlspecialchars($title, ENT_QUOTES) . "</h1>";
    echo "<div class='meta-row'>";
    echo "<span class='meta-item'>✍️ " . htmlspecialchars($d['author'], ENT_QUOTES) . "</span>";
    echo "<span class='meta-item'>📅 " . (int)$d['year'] . "</span>";
    echo "<span class='meta-item'>🏷️ " . htmlspecialchars($d['genre'], ENT_QUOTES) . "</span>";
    echo "</div>";
    echo "<div class='summary-box'>" . htmlspecialchars($d['summary'], ENT_QUOTES) . "</div>";
    echo "<a class='action-btn' href='?book=" . rawurlencode($title) . "'>📖 Read More</a>";
    echo "</div></div>";
}

function preserveVal(string $k): string {
    return isset($_GET[$k]) ? 'value="' . htmlspecialchars((string)$_GET[$k], ENT_QUOTES) . '"' : '';
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Digital Library Organizer</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .logo {
            font-size: 28px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .subtitle {
            font-size: 12px;
            opacity: 0.9;
            margin-top: 5px;
        }
        
        .search-form {
            display: flex;
            gap: 10px;
        }
        
        .search-input {
            padding: 10px 15px;
            border: none;
            border-radius: 25px;
            width: 250px;
            font-size: 14px;
            outline: none;
        }
        
        .search-btn {
            padding: 10px 25px;
            background: white;
            color: #667eea;
            border: none;
            border-radius: 25px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .main-layout {
            display: grid;
            grid-template-columns: 280px 1fr 320px;
            gap: 0;
            min-height: 600px;
        }
        
        @media (max-width: 1200px) {
            .main-layout {
                grid-template-columns: 1fr;
            }
            .sidebar, .right-panel { display: none; }
        }
        
        .sidebar {
            background: #f8f9fa;
            padding: 25px 20px;
            overflow-y: auto;
            border-right: 2px solid #e9ecef;
        }
        
        .category-section {
            margin-bottom: 25px;
        }
        
        .category-header {
            font-weight: bold;
            font-size: 14px;
            text-transform: uppercase;
            color: #667eea;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #667eea;
        }
        
        .category-books {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .book-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 15px;
            text-decoration: none;
            color: #333;
            border-radius: 10px;
            transition: all 0.3s;
            background: white;
            border: 1px solid #e9ecef;
        }
        
        .book-link:hover {
            background: #667eea;
            color: white;
            transform: translateX(5px);
        }
        
        .book-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: bold;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .book-icon {
            font-size: 18px;
        }
        
        .content {
            padding: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .empty-state {
            text-align: center;
            color: #6c757d;
        }
        
        .empty-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }
        
        .empty-state h2 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .book-display {
            display: flex;
            gap: 40px;
            max-width: 800px;
            width: 100%;
        }
        
        .book-cover {
            width: 250px;
            height: 350px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
            flex-shrink: 0;
        }
        
        .cover-text {
            color: white;
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            z-index: 2;
            position: relative;
        }
        
        .cover-shine {
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: rotate(45deg);
        }
        
        .book-info {
            flex: 1;
        }
        
        .book-info h1 {
            color: #333;
            margin-bottom: 15px;
            font-size: 32px;
        }
        
        .meta-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .meta-item {
            background: #f8f9fa;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
            color: #495057;
        }
        
        .summary-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            line-height: 1.6;
            color: #495057;
            margin-bottom: 25px;
            border-left: 4px solid #667eea;
        }
        
        .action-btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        
        .right-panel {
            background: #f8f9fa;
            padding: 25px 20px;
            overflow-y: auto;
            border-left: 2px solid #e9ecef;
        }
        
        .panel-section {
            margin-bottom: 30px;
        }
        
        .panel-title {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 15px;
            color: #333;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .search-result {
            background: white;
            padding: 15px;
            border-radius: 10px;
            border-left: 4px solid #667eea;
            margin-bottom: 15px;
        }
        
        .search-query {
            font-size: 13px;
            color: #6c757d;
            margin-bottom: 8px;
        }
        
        .search-status {
            font-size: 14px;
            color: #333;
        }
        
        .search-status a {
            color: #667eea;
            text-decoration: none;
            font-weight: bold;
        }
        
        .divider {
            border: none;
            border-top: 2px solid #e9ecef;
            margin: 20px 0;
        }
        
        .title-list {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .title-item {
            background: white;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 8px;
            border: 1px solid #e9ecef;
            font-size: 14px;
            color: #495057;
        }
        
        .tip-box {
            background: #fff3cd;
            padding: 12px;
            border-radius: 8px;
            font-size: 12px;
            color: #856404;
            margin-top: 20px;
        }
        
        .error-card {
            background: #f8d7da;
            color: #721c24;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            font-size: 18px;
        }
        
        .footer {
            text-align: center;
            padding: 20px;
            color: #6c757d;
            font-size: 13px;
            background: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <div class="logo">📚 Digital Library</div>
                <div class="subtitle">Powered by Recursion • Hash Table • BST</div>
            </div>
            <form method="get" class="search-form">
                <input name="search" class="search-input" placeholder="Search exact title..." <?php echo preserveVal('search'); ?>>
                <button class="search-btn" type="submit">🔍 Search</button>
            </form>
        </div>

        <div class="main-layout">
            <div class="sidebar">
                <div class="panel-title">📂 Browse Categories</div>
                <?php renderSidebar($library, $selected); ?>
            </div>

            <div class="content">
                <?php renderBookCard($selected, $bookDetails); ?>
            </div>

            <div class="right-panel">
                <div class="panel-section">
                    <div class="panel-title">🔍 Search Results</div>
                    <?php
                    if ($searchQuery !== null) {
                        echo "<div class='search-result'>";
                        echo "<div class='search-query'>Query: <strong>" . htmlspecialchars($searchQuery, ENT_QUOTES) . "</strong></div>";
                        $found = $bst->search($searchQuery);
                        if ($found) {
                            echo "<div class='search-status'>✅ Found! <a href='?book=" . rawurlencode($searchQuery) . "'>View Book</a></div>";
                        } else {
                            echo "<div class='search-status'>❌ Not found. Please check the exact title.</div>";
                        }
                        echo "</div>";
                    } else {
                        echo "<div class='search-result'>";
                        echo "<div class='search-status'>No search performed yet.</div>";
                        echo "</div>";
                    }
                    ?>
                </div>

                <hr class="divider">

                <div class="panel-section">
                    <div class="panel-title">📖 All Titles (A-Z)</div>
                    <div class="title-list">
                        <?php
                        $bst->inorder(function($t) {
                            echo "<div class='title-item'>" . htmlspecialchars($t, ENT_QUOTES) . "</div>";
                        });
                        ?>
                    </div>
                </div>

                <div class="tip-box">
                    💡 Tip: Click any book from the left sidebar to view full details
                </div>
            </div>
        </div>

        <div class="footer">
            Built with PHP • Digital Library Organizer System
        </div>
    </div>
</body>
</html>
