<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <p>Welcome to the Abuse Reports management section. Here, you can view, analyze, and take action on reports submitted by users. Use the tools provided to maintain a safe and respectful community.</p>

    <div class="kr-accordion">
        <div class="kr-accordion-item">
            <button class="kr-accordion-button">Viewing Reports</button>
            <div class="kr-accordion-content">
                <p>This section lists all the abuse reports submitted by users. You can review each report's details, including the reason for the report, the user who reported it, and the reported content or comment ID.</p>

                <!-- Filter Form with Bootstrap 5.3 Styling -->
                <div class="kr-report-filters mt-3 mb-3">
                    <form id="kr-report-filter-form" class="row g-3 align-items-center">
                        <div class="col-auto">
                            <label for="report-status-filter" class="col-form-label">Filter reports:</label>
                        </div>
                        <div class="col-auto">
                            <select id="kr-report-status-filter" class="form-select">
                                <option value="all">All</option>
                                <option value="open">Open</option>
                                <option value="reviewing">Reviewing</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="button" id="krFilterReports" class="btn btn-primary">Apply Filter</button>
                        </div>
                    </form>
                </div>


                <!-- Placeholder for Dynamic Table -->
                <div id="abuse-reports-table-container">
                    <!-- The table will be dynamically inserted here -->
                </div>

                <p>After reviewing a report, you can take appropriate action. Actions include marking the report as reviewed, editing the comment content, or banning the user from future postings.</p>
                <strong>Note:</strong> Please ensure to review reports carefully before taking any action to avoid mistakenly penalizing innocent users.

            </div>
        </div>
        <div id="kr-report-details-dialog" title="Report Details" style="display:none;">
            <!-- Details will be dynamically filled based on the clicked "Review" button -->
        </div>

    </div>
</div>