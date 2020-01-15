DROP TABLE IF EXISTS plugin_agiledashboard_planning;
DROP TABLE IF EXISTS plugin_agiledashboard_planning_backlog_tracker;
DROP TABLE IF EXISTS plugin_agiledashboard_semantic_initial_effort;
DROP TABLE IF EXISTS plugin_agiledashboard_criteria;
DROP TABLE IF EXISTS plugin_agiledashboard_configuration;
DROP TABLE IF EXISTS plugin_agiledashboard_kanban_configuration;
DROP TABLE IF EXISTS plugin_agiledashboard_kanban_configuration_column;
DROP TABLE IF EXISTS plugin_agiledashboard_scrum_mono_milestones;
DROP TABLE IF EXISTS plugin_agiledashboard_kanban_widget;
DROP TABLE IF EXISTS plugin_agiledashboard_kanban_widget_config;
DROP TABLE IF EXISTS plugin_agiledashboard_semantic_done;
DROP TABLE IF EXISTS plugin_agiledashboard_tracker_field_burnup_cache;
DROP TABLE IF EXISTS plugin_agiledashboard_kanban_recently_visited;
DROP TABLE IF EXISTS plugin_agiledashboard_planning_explicit_backlog_usage;
DROP TABLE IF EXISTS plugin_agiledashboard_planning_artifacts_explicit_backlog;
DROP TABLE IF EXISTS plugin_agiledashboard_burnup_projects_count_mode;
DROP TABLE IF EXISTS plugin_agiledashboard_kanban_tracker_reports;
DROP TABLE IF EXISTS plugin_agiledashboard_tracker_field_burnup_cache_subelements;
DROP TABLE IF EXISTS plugin_agiledashboard_tracker_workflow_action_add_top_backlog;

DELETE FROM permissions_values WHERE permission_type IN ('PLUGIN_AGILEDASHBOARD_PLANNING_PRIORITY_CHANGE');

DELETE FROM service WHERE short_name='plugin_agiledashboard';
