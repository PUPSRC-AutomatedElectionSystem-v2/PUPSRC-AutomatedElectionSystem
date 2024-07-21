<?php

/* CONSTANTS */


define('ROLE_HEAD_ADMIN', 'head_admin');
define('ROLE_ADMIN', 'admin');
define('ROLE_STUDENT_VOTER', 'student_voter');


// All Actions
define('LOGIN', 'login');
define('LOGOUT', 'logout');


// Specific to Student Voter
define('VOTED', 'voted');
define('UPDATE_EMAIL', 'update_email');
define('CHANGE_PASSWORD', 'change_password');
define('TRANSFER_ORG', 'transfer_org');


// Specific to Head Admin and Admin
define('UPDATE_PROFILE', 'update_profile');
define('ADD_CANDIDATE', 'add_candidate');
define('ADD_MULTIPLE_CANDIDATES', 'add_multiple_candidates');
define('DELETE_CANDIDATE', 'delete_candidate');
define('DELETE_MULTIPLE_CANDIDATES', 'delete_multiple_candidates');
define('PERMANENT_DELETE_CANDIDATE', 'permanent_delete_candidate');
define('PERMANENT_DELETE_MULTIPLE_CANDIDATES', 'permanent_delete_multiple_candidates');
define('UPDATE_CANDIDATE_INFO', 'update_candidate_info');
define('APPROVE_VOTER', 'approve_voter');
define('REJECT_VOTER', 'reject_voter');
define('DELETE_VOTER', 'delete_voter');
define('DELETE_MULTIPLE_VOTERS', 'delete_multiple_voters');
define('PERMANENT_DELETE_VOTER', 'permanent_delete_voter');
define('PERMANENT_DELETE_MULTIPLE_VOTERS', 'permanent_delete_multiple_voters');
define('CREATE_CANDIDATE_POSITION', 'create_candidate_position');
define('ADD_VOTING_GUIDELINE', 'add_voting_guideline');
define('SET_VOTING_SCHEDULE', 'set_voting_schedule');
define('UPDATE_VOTING_SCHEDULE', 'update_voting_schedule');
define('SET_REGISTRATION_SCHEDULE', 'set_registration_schedule');
define('UPDATE_REGISTRATION_SCHEDULE', 'update_registration_schedule');
define('IMPORT_MEMBER_LIST', 'import_member_list');
define('ADD_FAQ', 'add_faq');
define('UPDATE_FAQ', 'update_faq');
define('DELETE_FAQ', 'delete_faq');


// Specific to Head Admin
define('CREATE_ADMIN_ACCOUNT', 'create_admin_account');
define('CREATE_HEAD_ADMIN_ACCOUNT', 'create_head_admin_account');
define('DELETE_ADMIN_ACCOUNT', 'delete_admin_account');
define('DELETE_MULTIPLE_ADMIN_ACCOUNTS', 'delete_multiple_admin_accounts');
define('PERMANENT_DELETE_ADMIN_ACCOUNT', 'permanent_delete_admin_account');
define('PERMANENT_DELETE_MULTIPLE_ADMIN_ACCOUNTS', 'permanent_delete_multiple_admin_accounts');


/* Action logs message */

const STUDENT_VOTER_ACTIONS = array(
    LOGIN => 'You <strong>logged</strong> in to your account.',
    LOGOUT => 'You <strong>logged</strong> out of your account.',
    VOTED => 'You <strong>casted</strong> your vote.',
    UPDATE_EMAIL => 'You <strong>changed</strong> your email address.',
    CHANGE_PASSWORD => '<strong>You changed</strong> your password.',
    TRANSFER_ORG => 'You <strong>transferred</strong> to another organization.'
);


const ADMIN_ACTIONS = array(
    LOGIN => 'You <strong>logged</strong> in to your account.',
    LOGOUT => 'You <strong>logged</strong> out of your account.',
    UPDATE_PROFILE => 'You <strong>updated</strong> your profile information.',
    CHANGE_PASSWORD => 'You <strong>changed</strong> your password.',
    ADD_CANDIDATE => 'You <strong>added</strong> a new candidate.',
    ADD_MULTIPLE_CANDIDATES => 'You <strong>added</strong> multiple candidates.',
    DELETE_CANDIDATE => 'You <strong>deleted</strong> a candidate.',
    DELETE_MULTIPLE_CANDIDATES => 'You <strong>deleted</strong> multiple candidates.',
    PERMANENT_DELETE_CANDIDATE => 'You <strong>permanently deleted</strong> a candidate.',
    PERMANENT_DELETE_MULTIPLE_CANDIDATES => 'You <strong>permanently deleted</strong> multiple candidates.',
    UPDATE_CANDIDATE_INFO => 'You <strong>updated</strong> a candidate information.',
    APPROVE_VOTER => 'You <strong>approved</strong> a pending voter registration.',
    REJECT_VOTER => 'You <strong>rejected</strong> a pending voter registration.',
    DELETE_VOTER => 'You <strong>deleted</strong> a voter account.',
    DELETE_MULTIPLE_VOTERS => 'You <strong>deleted</strong> multiple voter accounts.',
    PERMANENT_DELETE_VOTER => 'You <strong>permanently deleted</strong> a voter account.',
    PERMANENT_DELETE_MULTIPLE_VOTERS => 'You <strong>permanently deleted</strong> multiple voter accounts.',
    CREATE_CANDIDATE_POSITION => 'You <strong>created</strong> a new candidate position.',
    ADD_VOTING_GUIDELINE => 'You <strong>added</strong> new voting guideline/s.',
    SET_VOTING_SCHEDULE => 'You <strong>set</strong> a voting schedule.',
    UPDATE_VOTING_SCHEDULE => 'You <strong>updated</strong> the voting schedule.',
    SET_REGISTRATION_SCHEDULE => 'You <strong>set</strong> a registration schedule.',
    UPDATE_REGISTRATION_SCHEDULE => 'You <strong>updated</strong> the registration schedule.',
    IMPORT_MEMBER_LIST => 'You <strong>imported</strong> an Excel/CSV file.',
    ADD_FAQ => 'You <strong>added</strong> a FAQ item.',
    UPDATE_FAQ => 'You <strong>updated</strong> a FAQ item.',
    DELETE_FAQ => 'You <strong>updated</strong> a FAQ item.'
);


const HEAD_ADMIN_ACTIONS = array(
    LOGIN => 'You <strong>logged</strong> in to your account.',
    LOGOUT => 'You <strong>logged</strong> out of your account.',
    UPDATE_PROFILE => 'You <strong>updated</strong> your profile information.',
    CHANGE_PASSWORD => 'You <strong>changed</strong> your password.',
    ADD_CANDIDATE => 'You <strong>added</strong> a new candidate.',
    ADD_MULTIPLE_CANDIDATES => 'You <strong>added</strong> multiple candidates.',
    DELETE_CANDIDATE => 'You <strong>deleted</strong> a candidate.',
    DELETE_MULTIPLE_CANDIDATES => 'You <strong>deleted</strong> multiple candidates.',
    PERMANENT_DELETE_CANDIDATE => 'You <strong>permanently deleted</strong> a candidate.',
    PERMANENT_DELETE_MULTIPLE_CANDIDATES => 'You <strong>permanently deleted</strong> multiple candidates.',
    UPDATE_CANDIDATE_INFO => 'You <strong>updated</strong> a candidate information.',
    APPROVE_VOTER => 'You <strong>approved</strong> a pending voter registration.',
    REJECT_VOTER => 'You <strong>rejected</strong> a pending voter registration.',
    DELETE_VOTER => 'You <strong>deleted</strong> a voter account.',
    DELETE_MULTIPLE_VOTERS => 'You <strong>deleted</strong> multiple voter accounts.',
    PERMANENT_DELETE_VOTER => 'You <strong>permanently deleted</strong> a voter account.',
    PERMANENT_DELETE_MULTIPLE_VOTERS => 'You <strong>permanently deleted</strong> multiple voter accounts.',
    CREATE_CANDIDATE_POSITION => 'You <strong>created</strong> a new candidate position.',
    ADD_VOTING_GUIDELINE => 'You <strong>added</strong> new voting guideline/s.',
    SET_VOTING_SCHEDULE => 'You <strong>set</strong> a voting schedule.',
    UPDATE_VOTING_SCHEDULE => 'You <strong>updated</strong> the voting schedule.',
    SET_REGISTRATION_SCHEDULE => 'You <strong>set</strong> a registration schedule.',
    UPDATE_REGISTRATION_SCHEDULE => 'You <strong>updated</strong> the registration schedule.',
    IMPORT_MEMBER_LIST => 'You <strong>imported</strong> an Excel/CSV file.',
    ADD_FAQ => 'You <strong>added</strong> a FAQ item.',
    UPDATE_FAQ => 'You <strong>updated</strong> a FAQ item.',
    DELETE_FAQ => 'You <strong>deleted</strong> a FAQ item.',
    CREATE_ADMIN_ACCOUNT => 'You <strong>created</strong> a new admin account.',
    CREATE_HEAD_ADMIN_ACCOUNT => 'You <strong>created</strong> a new head admin account.',
    DELETE_ADMIN_ACCOUNT => 'You <strong>deleted</strong> an admin account.',
    DELETE_MULTIPLE_ADMIN_ACCOUNTS => 'You <strong>deleted</strong> multiple admin accounts.',
    PERMANENT_DELETE_ADMIN_ACCOUNT => 'You <strong>permanently deleted</strong> an admin account.',
    PERMANENT_DELETE_MULTIPLE_ADMIN_ACCOUNTS => 'You <strong>permanently deleted</strong> multiple admin accounts.'
);
