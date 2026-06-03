<?php

require_once '../../includes/admin_auth.php';
require_once '../../config/db.php';


/* AUTOCOMPLETE */

if(isset($_GET['term'])){

    $term = trim($_GET['term']);

    $data = [];

    $stmt = $conn->prepare("

        SELECT DISTINCT
            full_name,
            email

        FROM users

        WHERE
            (
                full_name LIKE CONCAT('%', ?, '%')
                OR
                email LIKE CONCAT('%', ?, '%')
            )
            AND role IN ('admin', 'staff')
           
        LIMIT 10

        ");

    $stmt->bind_param(
        "ss",
        $term,
        $term
    );

    $stmt->execute();

    $result = $stmt->get_result();

    while($row = $result->fetch_assoc()){

        $data[] = [
            'type' => 'User',
            'text' => $row['full_name']
        ];

        $data[] = [
            'type' => 'Email',
            'text' => $row['email']
        ];
    }

    header('Content-Type: application/json');

    echo json_encode($data);

    exit();
}


/* SEARCH */

$search = trim($_GET['search'] ?? '');
$role = $_GET['role'] ?? '';


$sql = "
    SELECT
        id,
        full_name,
        email,
        role,
        status,
        created_at
    FROM users
    WHERE role IN ('admin','staff')
";

/* ROLE FILTER */
if($role != ''){
    $sql .= " AND role = '$role' ";
}

/* SEARCH FILTER */
if($search != ''){
    $search = $conn->real_escape_string($search);

    $sql .= "
        AND (
            full_name LIKE '%$search%'
            OR email LIKE '%$search%'
        )
    ";
}


$sql .= "
    ORDER BY 
        CASE 
            WHEN role = 'admin' THEN 1
            WHEN role = 'staff' THEN 2
        END,
        id DESC
";


$users = $conn->query($sql);

?>

<!DOCTYPE html>
<html>

<head>

    <title>
        Admin Users - GSC
    </title>

    <link
        href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css'
        rel='stylesheet'
    >

    <style>

        body{

            margin:0;

            font-family:'Segoe UI',sans-serif;

            background:
            linear-gradient(
                135deg,
                #f8fafc,
                #eef2ff
            );

            min-height:100vh;
        }

        .main{

            margin-left:260px;
            padding:40px;
        }

        .top-bar{

            display:flex;
            justify-content:space-between;
            align-items:center;

            margin-bottom:30px;
        }

        .page-title{

            font-size:42px;
            font-weight:700;
            color:#111827;
        }

        .add-btn{
            background:#f5c518;
            color:#111;

            text-decoration:none;

            padding:12px 20px;

            border-radius:16px;

            font-weight:700;

            transition:0.25s;
        }

        .add-btn:hover{
            background:#ffd93d;
            transform:translateY(-2px);
        }

        .search-card{

            margin-bottom:25px;
        }

        .search-input{

            max-width:1000px;
            height:50px;
        }

        .table-card{

            background:white;

            border-radius:24px;

            padding:25px;

            box-shadow:
            0 10px 25px rgba(0,0,0,0.08);
        }

        .table{

            margin-bottom:0;
        }

        .table thead th{

            border:none;

            padding:18px;

            background:#f8fafc;

            color:#374151;

            font-weight:700;
        }

        .table tbody td{

            padding:18px;
            vertical-align:middle;
        }

        .table tbody tr{

            transition:0.2s;
        }

        .table tbody tr:hover{

            background:#f9fafb;
        }

        .user-name{

            font-weight:700;
            color:#111827;
        }

        .email{

            color:#666;
            font-size:14px;
        }

        .action-btn{

            border:none;

            padding:9px 14px;

            border-radius:12px;

            font-size:14px;

            font-weight:600;

            text-decoration:none;

            transition:0.2s;
        }

        .btn-view{

            background:#dbeafe;
            color:#1d4ed8;
        }

        .action-btn:hover{

            transform:scale(1.05);
        }

        .btn-edit{
            background:#e0f2fe;
            color:#0369a1;
        }

        .btn-edit:hover{
            background:#bae6fd;
            color:#075985;
        }

        .empty-text{

            text-align:center;

            padding:40px;

            color:#777;
        }

        #suggestions{

            position:absolute;

            top:100%;

            left:0;

            right:0;

            background:white;

            border-radius:14px;

            margin-top:6px;

            overflow:hidden;

            z-index:9999;

            box-shadow:
            0 10px 25px rgba(0,0,0,.08);

            display:none;
        }

        .suggestion-item{

            padding:12px 16px;

            cursor:pointer;

            transition:.2s;
        }

        .suggestion-item:hover,
        .suggestion-item.active{

            background:#eef2ff;
        }

        .btn-danger{
            background:#fee2e2;
            color:#dc2626;
        }

        .btn-success{
            background:#dcfce7;
            color:#16a34a;
        }

    </style>

</head>

<body>

<?php include '../../includes/admin_sidebar.php'; ?>

<div class="main">

    <?php if(isset($_GET['updated'])): ?>

    <div
        id="successToast"
        class="toast align-items-center text-bg-success border-0 position-fixed top-0 end-0 m-4 show"
        style="z-index:9999;"
    >
        <div class="d-flex">S

            <div class="toast-body">

                Updated successfully.

            </div>

        </div>
    </div>

    <?php endif; ?>

    <div class="top-bar">

        <div class="page-title">

            Staff Management

        </div>

        <a href="add_staff.php"
            class="add-btn">
                + Add Users
            </a>

    </div>

    <div class="search-card position-relative">

        <form method="GET" class="d-flex gap-3">

            <select name="role" class="form-select w-auto">
                <option value="">All</option>
                <option value="admin" <?= ($role ?? '') == 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="staff" <?= ($role ?? '') == 'staff' ? 'selected' : '' ?>>Staff</option>
            </select>

            <div class="position-relative flex-grow-1">

                <input
                    type="text"
                    id="searchInput"
                    name="search"
                    class="form-control search-input"
                    placeholder="Search user name or email..."
                    autocomplete="off"
                    value="<?= htmlspecialchars($search) ?>"
                >

                <div id="suggestions"></div>

            </div>

            <button class="btn btn-dark px-4">

                Search

            </button>

            <?php if($search != ''): ?>

                <a
                    href="staffs.php"
                    class="btn btn-secondary d-flex align-items-center justify-content-center"
                >
                    Reset
                </a>

            <?php endif; ?>

        </form>

    </div>

    <div class="table-card">

        <table class="table align-middle">

            <thead>

                <tr>

                    <th>
                        User
                    </th>

                    <th>
                        Role
                    </th>

                    <th>
                        Created
                    </th>

                    <th width="180">
                        Action
                    </th>

                    <th>
                        Status
                    </th>

                </tr>

            </thead>

            <tbody>

                <?php if($users->num_rows > 0): ?>

                    <?php while($u = $users->fetch_assoc()): ?>
                    <tr>

                        <td>
                            <div class="user-name">
                                <?= htmlspecialchars($u['full_name']) ?>
                            </div>
                            <div class="email">
                                <?= htmlspecialchars($u['email']) ?>
                            </div>
                        </td>

                        <td>
                            <?= ucfirst($u['role']) ?>
                        </td>

                        <td>
                            <?= date('d M Y', strtotime($u['created_at'])) ?>
                        </td>

                        <td>
                            <div class="d-flex gap-2 align-items-center">

                                <a href="view_staff.php?id=<?= $u['id'] ?>"
                                    class="action-btn btn-view">
                                        View
                                </a>

                                <a href="edit_staff.php?id=<?= $u['id'] ?>"
                                    class="action-btn btn-edit">
                                        Edit
                                    </a>

                                <button 
                                    class="action-btn toggle-btn <?= $u['status'] == 'active' ? 'btn-danger' : 'btn-success' ?>"
                                    data-id="<?= $u['id'] ?>">
                                    <?= $u['status'] == 'active' ? 'Deactivate' : 'Activate' ?>
                                </button>

                            </div>
                        </td>

                        <td>
                            <?php if($u['status'] == 'active'): ?>
                                <span class="status-badge badge bg-success">
                                    Active
                                </span>
                            <?php else: ?>
                                <span class="status-badge badge bg-secondary">
                                    Inactive
                                </span>
                            <?php endif; ?>
                        </td>

                    </tr>
                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>

                        <td colspan="4">

                            <div class="empty-text">

                                No staff found.

                            </div>

                        </td>

                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

<script>

function getActionText(text) {

    const t = text.toLowerCase().trim();

    if (t === 'deactivate') return 'deactivate';
    if (t === 'activate') return 'activate';

    return 'update';
}

const searchInput =
    document.getElementById('searchInput');

const suggestions =
    document.getElementById('suggestions');

let currentIndex = -1;

searchInput.addEventListener('input', () => {

    const keyword =
        searchInput.value.trim();

    if(keyword.length < 2){

        suggestions.style.display = 'none';
        return;
    }

    fetch(
        'staffs.php?term=' +
        encodeURIComponent(keyword)
    )
    .then(res => res.json())
    .then(data => {

        suggestions.innerHTML = '';

        currentIndex = -1;

        if(data.length === 0){

            suggestions.style.display = 'none';
            return;
        }

        data.forEach(item => {

            const div =
                document.createElement('div');

            div.className =
                'suggestion-item';

            div.textContent =
                item.text;

            div.onclick = () => {

                searchInput.value =
                    item.text;

                suggestions.style.display =
                    'none';

                searchInput.form.submit();
            };

            suggestions.appendChild(div);

        });

        suggestions.style.display =
            'block';

    });

});

searchInput.addEventListener('keydown', e => {

    const items =
        document.querySelectorAll(
            '.suggestion-item'
        );

    if(!items.length) return;

    if(e.key === 'ArrowDown'){

        e.preventDefault();

        currentIndex++;

        if(currentIndex >= items.length){

            currentIndex = 0;
        }

        updateActive(items);
    }

    else if(e.key === 'ArrowUp'){

        e.preventDefault();

        currentIndex--;

        if(currentIndex < 0){

            currentIndex =
                items.length - 1;
        }

        updateActive(items);
    }

    else if(e.key === 'Enter'){

        if(currentIndex >= 0){

            e.preventDefault();

            items[currentIndex].click();
        }
    }

});

function updateActive(items){

    items.forEach(item =>
        item.classList.remove('active')
    );

    items[currentIndex]
        .classList.add('active');
}

document.addEventListener('click', e => {

    if(
        !searchInput.contains(e.target)
        &&
        !suggestions.contains(e.target)
    ){

        suggestions.style.display =
            'none';
    }

});

let selectedUserId = null;
let selectedBtn = null;

document.querySelectorAll('.toggle-btn').forEach(btn => {

    btn.addEventListener('click', function () {

        selectedUserId = this.dataset.id;
        selectedBtn = this;

        // 👉 取 user name（从 table row）
        const userName = this
            .closest('tr')
            .querySelector('.user-name')
            .innerText;

        // 👉 更新 modal 文案
        const action = getActionText(this.textContent);

        document.getElementById('modalText').innerHTML =
            `Are you sure you want to <b>${action}</b> <b>"${userName}"</b>?`;

        const modal = new bootstrap.Modal(
            document.getElementById('confirmModal')
        );

        modal.show();
    });

});

</script>

<div class="modal fade" id="confirmModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Confirm Action</h5>
      </div>

      <div class="modal-body" id="modalText">
        Are you sure?
    </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">
          Cancel
        </button>

        <button class="btn btn-danger" id="confirmBtn">
            Confirm
        </button>
      </div>

    </div>
  </div>
</div>

<script>
const confirmBtn = document.getElementById('confirmBtn');

confirmBtn.addEventListener('click', function () {

console.log('CONFIRM CLICKED');
    console.log('User ID:', selectedUserId);

    confirmBtn.disabled = true;
    confirmBtn.innerHTML = 'Processing...';

    fetch('toggle_staff.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
            id: selectedUserId
        })
    })
    .then(res => res.json())
    .then(data => {

        if (!data.success) {

            document.getElementById('modalText').innerHTML =
                `<div class="alert alert-danger mb-0">
                    ${data.message}
                </div>`;

            return;
        }

        const row = selectedBtn.closest('tr');
        const badge = row.querySelector('.status-badge');

        if (data.new_status === 'active') {

            selectedBtn.textContent = 'Deactivate';
            selectedBtn.classList.replace(
                'btn-success',
                'btn-danger'
            );

            badge.className =
                'status-badge badge bg-success';

            badge.textContent =
                'Active';

        } else {

            selectedBtn.textContent =
                'Activate';

            selectedBtn.classList.replace(
                'btn-danger',
                'btn-success'
            );

            badge.className =
                'status-badge badge bg-secondary';

            badge.textContent =
                'Inactive';
        }

        bootstrap.Modal.getInstance(
            document.getElementById('confirmModal')
        ).hide();

            const toast = document.createElement('div');

            toast.className =
            'toast align-items-center text-bg-success border-0 position-fixed top-0 end-0 m-3 show';

            toast.style.zIndex = '9999';

            toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    Updated successfully.
                </div>
            </div>
            `;

            document.body.appendChild(toast);

            setTimeout(() => {
                toast.remove();
            }, 3000);

    })
    .finally(() => {

        confirmBtn.disabled = false;
        confirmBtn.innerHTML = 'Confirm';
    });

});
</script>

<script>

setTimeout(() => {

    const toast =
        document.getElementById('successToast');

    if(toast){

        toast.remove();
    }

},3000);

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>