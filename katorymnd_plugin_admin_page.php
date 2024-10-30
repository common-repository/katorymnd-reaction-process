<?php
// Fetch plugin details dynamically here (simulated with static array for the example)
$plugins = [
    [
        'name' => 'Katorymnd reaction process',
        'description' => 'The plugin introduces a dynamic and interactive layer to your WordPress site, allowing users to express their feelings and thoughts on your content through a variety of reaction options.',
        'version' => '1.2.0',
        'status' => 'Active',
        'slug' => 'katorymnd-reaction-settings',
    ],
    // Simulate more plugins as needed
];
?>
<div class="wrap">
    <h1><?php echo esc_html__('Katorymnd Plugins', 'katorymnd-reaction-process'); ?></h1>
    <p><?php echo esc_html__('Here you can manage all the plugins developed by Katorymnd Freelancer.', 'katorymnd-reaction-process'); ?>
    </p>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php echo esc_html__('Name', 'katorymnd-reaction-process'); ?></th>
                <th><?php echo esc_html__('Description', 'katorymnd-reaction-process'); ?></th>
                <th><?php echo esc_html__('Version', 'katorymnd-reaction-process'); ?></th>
                <th><?php echo esc_html__('Status', 'katorymnd-reaction-process'); ?></th>
                <th><?php echo esc_html__('Actions', 'katorymnd-reaction-process'); ?></th>
            </tr>
        </thead>
        <tbody id="the-list">
            <?php foreach ($plugins as $plugin) : ?>
            <tr>
                <td><?php echo esc_html($plugin['name']); ?></td>
                <td><?php echo esc_html($plugin['description']); ?></td>
                <td><?php echo esc_html($plugin['version']); ?></td>
                <td><?php echo esc_html($plugin['status']); ?></td>
                <td>
                    <a
                        href="<?php echo esc_url(admin_url('admin.php?page=' . $plugin['slug'])); ?>"><?php echo esc_html__('Settings', 'katorymnd-reaction-process'); ?></a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>