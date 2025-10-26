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
    echo "<ul class='list-unstyled ps-1'>";
    foreach ($tree as $k => $v) {
        if (is_array($v)) {
            echo "<li class='sidebar-category'>" . htmlspecialchars($k, ENT_QUOTES) . "</li>";
            echo "<li>";
            renderSidebar($v, $selected);
            echo "</li>";
        } else {
            $title = (string)$v;
            $active = ($selected !== null && strcasecmp($selected, $title) === 0) ? "active" : "";
            $url = "?book=" . rawurlencode($title);
            echo "<li class='sidebar-book'><a class='sidebar-link $active' href='$url'>" . htmlspecialchars($title, ENT_QUOTES) . "</a></li>";
        }
    }
    echo "</ul>";
}

function renderBookCard(?string $title, array $details) {
    if ($title === null) {
        echo "<div class='placeholder h-100 d-flex flex-column justify-content-center align-items-center text-center'>";
        echo "<div style='max-width:420px;'><h3 class='mb-2'>Welcome to your Library</h3>";
        echo "<p class='mb-0 text-muted'>Select a book from the left to view details, or search on the right.</p></div>";
        echo "</div>";
        return;
    }
    if (!array_key_exists($title, $details)) {
        echo "<div class='alert alert-warning'>Book not found.</div>";
        return;
    }
    $d = $details[$title];
    echo "<div class='book-card'>";
    echo "<div class='cover-placeholder'>".htmlspecialchars($title, ENT_QUOTES)."</div>";
    echo "<div class='book-meta'>";
    echo "<h2 class='book-title'>" . htmlspecialchars($title, ENT_QUOTES) . "</h2>";
    echo "<p class='small mb-1'><strong>Author:</strong> " . htmlspecialchars($d['author'], ENT_QUOTES) . " &bull; <strong>Year:</strong> " . (int)$d['year'] . "</p>";
    echo "<p class='mb-2'><strong>Genre:</strong> " . htmlspecialchars($d['genre'], ENT_QUOTES) . "</p>";
    echo "<p class='summary text-muted'>" . htmlspecialchars($d['summary'], ENT_QUOTES) . "</p>";
    echo "<div class='mt-3'><a class='btn btn-success btn-lg' href='?book=" . rawurlencode($title) . "'>Re-open</a></div>";
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
    <title>Digital Library Organizer</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --bg-1: #07160f;
            --panel: rgba(255,255,255,0.03);
            --accent: #1e8a3a;
            --accent-2: #0f5132;
            --muted: rgba(235,255,235,0.75);
        }
        body {
            background: linear-gradient(180deg, var(--bg-1), #0f2a1a 60%);
            color: #eaffe8;
            min-height: 100vh;
            padding: 18px;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
        }
        .app-shell { max-width:1200px; margin:0 auto; }
        .topbar {
            display:flex; align-items:center; justify-content:space-between;
            padding:10px 14px; border-radius:10px; background: linear-gradient(90deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
            margin-bottom:12px; border:1px solid rgba(255,255,255,0.03);
        }
        .brand { font-weight:700; color:var(--accent); }
        .sidebar {
            background: var(--panel); padding:12px; border-radius:10px; border:1px solid rgba(255,255,255,0.03);
            height: calc(80vh - 36px); overflow:auto;
        }
        .sidebar-category { font-weight:700; margin-top:8px; color:var(--muted); }
        .sidebar-book { margin-left:8px; margin-top:6px; }
        .sidebar-link { color:#dfffe6; text-decoration:none; display:block; padding:6px 8px; border-radius:8px; }
        .sidebar-link:hover { background: rgba(255,255,255,0.03); color:var(--accent-2); transform:translateX(6px); }
        .sidebar-link.active { background: var(--accent); color: white; box-shadow:0 6px 18px rgba(12,50,20,0.4); font-weight:700; }
        .center-panel {
            background: var(--panel); padding:18px; border-radius:10px; border:1px solid rgba(255,255,255,0.03);
            min-height: 80vh; display:flex; align-items:center; justify-content:center;
        }
        .book-card { display:flex; gap:20px; align-items:flex-start; width:100%; max-width:920px; }
        .cover-placeholder {
            width:220px; height:320px; border-radius:8px; background: linear-gradient(180deg, #164f2b, #0d3a20);
            display:flex; align-items:center; justify-content:center; color:#dfffe6; font-weight:700; padding:12px; text-align:center;
            box-shadow: 0 12px 32px rgba(0,0,0,0.6);
        }
        .book-meta { flex:1; }
        .book-title { margin-top:0; color:#eaffef; }
        .summary { color: rgba(220,255,235,0.85); }
        .right-panel {
            background: var(--panel); padding:12px; border-radius:10px; border:1px solid rgba(255,255,255,0.03);
            height: calc(80vh - 36px); overflow:auto;
        }
        .alpha-list { max-height: calc(80vh - 160px); overflow:auto; padding-right:6px; }
        .alpha-item { padding:8px; border-radius:8px; margin-bottom:6px; background: rgba(255,255,255,0.02); color: #effff2; }
    </style>
</head>
<body>
<div class="app-shell">
    <div class="topbar">
        <div class="d-flex align-items-center gap-3">
            <button id="toggleSidebar" class="btn btn-sm" style="background:var(--accent); color:white;">☰</button>
            <div>
                <div class="brand">Digital Library</div>
                <div class="small" style="color:var(--muted); font-size:13px;">Recursion • Hash Table • BST</div>
            </div>
        </div>
        <div>
            <form method="get" class="d-flex" style="gap:6px;">
                <input name="search" class="form-control form-control-sm" placeholder="Search exact title..." style="min-width:220px;" <?php echo preserveVal('search'); ?>>
                <button class="btn btn-success btn-sm" type="submit">Search</button>
            </form>
        </div>
    </div>

    <div class="row g-3">
        <div id="colSidebar" class="col-lg-3 d-none d-lg-block">
            <div class="sidebar">
                <div class="mb-2"><strong>Categories</strong></div>
                <?php renderSidebar($library, $selected); ?>
            </div>
        </div>

        <div class="col-lg-6 col-12">
            <div class="center-panel">
                <?php renderBookCard($selected, $bookDetails); ?>
            </div>
        </div>

        <div class="col-lg-3 col-12">
            <div class="right-panel">
                <div class="mb-3">
                    <h6 style="margin:0">Search Result</h6>
                    <?php
                    if ($searchQuery !== null) {
                        echo "<div class='mt-2'><small class='text-muted'>Query:</small> <strong>" . htmlspecialchars($searchQuery, ENT_QUOTES) . "</strong></div>";
                        $found = $bst->search($searchQuery);
                        if ($found) {
                            echo "<div class='alpha-item mt-2'>✅ Found — <a href='?book=" . rawurlencode($searchQuery) . "'>Open</a></div>";
                        } else {
                            echo "<div class='alpha-item mt-2'>❌ Not found. Try the full exact title.</div>";
                        }
                    } else {
                        echo "<div class='text-muted small'>No search performed yet. Use the search in the top bar.</div>";
                    }
                    ?>
                </div>

                <hr style="border-color: rgba(255,255,255,0.03)">

                <div>
                    <h6 style="margin:0 0 8px 0">All Titles (A → Z)</h6>
                    <div class="alpha-list">
                        <?php
                        $bst->inorder(function($t) {
                            echo "<div class='alpha-item'>" . htmlspecialchars($t, ENT_QUOTES) . "</div>";
                        });
                        ?>
                    </div>
                </div>

                <div class="mt-3 small text-muted">
                    Tip: Click a title from the left sidebar to view it in the center. Use search for exact-title lookup.
                </div>
            </div>
        </div>
    </div>

    <footer class="mt-3 text-center small text-muted">Built with plain PHP — Digital Library Organizer</footer>
</div>

<script>
document.getElementById('toggleSidebar').addEventListener('click', function() {
    const col = document.getElementById('colSidebar');
    if (!col) return;
    if (col.classList.contains('d-none')) {
        col.classList.remove('d-none');
        col.classList.add('d-block');
    } else {
        col.classList.add('d-none');
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>