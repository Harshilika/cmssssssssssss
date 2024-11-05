<?php

function get_the_teachers($args)
{
    return $args;
}

function get_the_classes()
{
    global $db_conn;
    $output = [];
    $query = mysqli_query($db_conn, 'SELECT * FROM classes');

    while ($row = mysqli_fetch_object($query)) {
        $output[] = $row;
    }

    return $output;
}

function get_post(array $args = [])
{
    global $db_conn;
    $condition = "WHERE 0 ";
    if (!empty($args)) {
        foreach ($args as $k => $v) {
            $v = mysqli_real_escape_string($db_conn, (string)$v);
            $condition_ar[] = "$k = '$v'";
        }
        if (!empty($condition_ar)) {
            $condition = "WHERE " . implode(" AND ", $condition_ar);
        }
    }

    $sql = "SELECT * FROM posts $condition";
    $query = mysqli_query($db_conn, $sql);
    return mysqli_fetch_object($query);
}

function get_posts(array $args = [], string $type = 'object')
{
    global $db_conn;
    $condition = "WHERE 0 ";
    if (!empty($args)) {
        foreach ($args as $k => $v) {
            $v = mysqli_real_escape_string($db_conn, (string)$v);
            $condition_ar[] = "$k = '$v'";
        }
        if (!empty($condition_ar)) {
            $condition = "WHERE " . implode(" AND ", $condition_ar);
        }
    }

    $sql = "SELECT * FROM posts $condition";
    $query = mysqli_query($db_conn, $sql);
    return data_output($query, $type);
}

function get_metadata($item_id, $meta_key = '', $type = 'object')
{
    global $db_conn;
    $query = mysqli_query($db_conn, "SELECT * FROM metadata WHERE item_id = $item_id");
    if (!empty($meta_key)) {
        $query = mysqli_query($db_conn, "SELECT * FROM metadata WHERE item_id = $item_id AND meta_key = '$meta_key'");
    }
    return data_output($query, $type);
}

function data_output($query, $type = 'object')
{
    $output = [];
    if ($type == 'object') {
        while ($result = mysqli_fetch_object($query)) {
            $output[] = $result;
        }
    } else {
        while ($result = mysqli_fetch_assoc($query)) {
            $output[] = $result;
        }
    }
    return $output;
}

function get_user_data($user_id, $type = 'object')
{
    global $db_conn;

    if (empty($user_id)) {
        return null; // Handle the case where user_id is not set
    }

    $stmt = $db_conn->prepare("SELECT * FROM accounts WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    return data_output($result, $type)[0];
}

function get_post_title($post_id = '')
{
    // Implementation needed
}

function get_users($args = [], $type = 'object')
{
    global $db_conn;
    $condition = "";
    if (!empty($args)) {
        foreach ($args as $k => $v) {
            $v = mysqli_real_escape_string($db_conn, (string)$v);
            $condition_ar[] = "$k = '$v'";
        }
        if (!empty($condition_ar)) {
            $condition = "WHERE " . implode(" AND ", $condition_ar);
        }
    }
    $query = mysqli_query($db_conn, "SELECT * FROM accounts $condition");
    return data_output($query, $type);
}

function get_user_metadata($user_id)
{
    global $db_conn;
    $output = [];
    $query = mysqli_query($db_conn, "SELECT * FROM usermeta WHERE `user_id` = '$user_id'");
    while ($result = mysqli_fetch_object($query)) {
        $output[$result->meta_key] = $result->meta_value;
    }

    return $output;
}

function get_usermeta($user_id, $meta_key, $single = true)
{
    global $db_conn;
    if (!empty($user_id) && !empty($meta_key)) {
        $query = mysqli_query($db_conn, "SELECT * FROM usermeta WHERE `user_id` = '$user_id' AND `meta_key` = '$meta_key'");
    } else {
        return false;
    }
    if ($single) {
        return mysqli_fetch_object($query)->meta_value;
    } else {
        return mysqli_fetch_object($query);
    }
}
?>
