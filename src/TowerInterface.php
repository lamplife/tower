<?php

declare(strict_types = 1);

/**
 * Author: 狂奔的螞蟻 <www.firstphp.com>
 * Date: 2019/12/17
 * Time: 1:12 PM
 */

namespace Firstphp\Tower;


interface TowerInterface
{


    public function getAccessToken();


    /**
     * 获取当前账号信息
     *
     * @return mixed
     */
    public function getUser();


    /**
     * 获取加入的团队列表
     *
     * @return mixed
     */
    public function getTeams();


    /**
     * 创建团队
     *
     * @param string $teamname 团队名称
     * @return mixed
     */
    public function createTeams(string $teamname);


    /**
     * 更改团队名称
     *
     * @param int $id 团队ID
     * @param string $teamname 团队名称
     * @return mixed
     */
    public function putTeams(int $id, string $teamname);


    /**
     * 获取通知列表
     *
     * @param string $team_id
     * @param int $page
     * @param int $size
     * @return mixed
     */
    public function getNotifications(string $team_id, int $page = 1, int $size);


    /**
     * 获取动态列表
     *
     * @param string $team_id 团队ID
     * @param int $last_item_id 上一次获取的最后一条动态 ID, 用于分页; 默认为空
     * @param int $limit 限制获取条数，默认：20
     * @param string $by_member 允许为空，指定成员产生的动态
     * @param string $by_project 允许为空，指定项目产生的动态
     * @return mixed
     */
    public function getEvents(string $team_id, int $last_item_id, int $limit, string $by_member, string $by_project);


    /**
     * 获取客户端直传签名(上传附件)
     *
     * @param string $team_id 团队ID
     * @param string $filename 文件名
     * @param int $byte_size 文件大小，单位字节
     * @param string $md5 文件内容的 md5 签名
     * @return mixed
     */
    public function getDirectUploads(string $team_id, string $filename, int $byte_size, string $md5);


    /**
     * 获取团队全部成员
     *
     * @param string $team_id 团队ID
     * @param int $page 从 1 开始计数
     * @return mixed
     */
    public function getMembers(string $team_id, int $page = 1);


    /**
     * 获取当前账户在团队中的信息
     *
     * @param string $team_id 团队ID
     * @return mixed
     */
    public function getMember(string $team_id);


    /**
     * 获取成员信息
     *
     * @param string $member_id 成员ID
     * @return mixed
     */
    public function getMemberInfo(string $member_id);


    /**
     * 获取指派给成员未完成任务
     *
     * @remark box 为分类属性，不要使用到期日作为分类。 0代表新任务，1代表今天，2代表接下来，3代表以后
     * @param string $member_id 成员ID
     * @return mixed
     */
    public function getAssignedUncompletedTodos(string $member_id);


    /**
     * 获取指派给成员已完成任务
     *
     * @param string $member_id 成员ID
     * @param int $page page 从 1 开始计数
     * @return mixed
     */
    public function getAssignedCompletedTodos(string $member_id, int $page = 1);


    /**
     * 获取成员创建的已完成任务
     *
     * @param string $member_id 团队成员ID
     * @param int $page 分页 page 从 1 开始计数
     * @return mixed
     */
    public function getCreatedCompletedTodos(string $member_id, int $page = 1);


    /**
     * 获取团队中所有项目
     *
     * @param string $team_id 团队ID
     * @return mixed
     */
    public function getProjects(string $team_id);


    /**
     * 创建项目
     *
     * @param string $team_id 团队ID
     * @param string $project_name 项目名称
     * @return mixed
     */
    public function createProjects(string $team_id, string $project_name);


    /**
     * 获取项目信息
     *
     * @param string $project_id 项目ID
     * @return mixed
     */
    public function getProjectsInfo(string $project_id);


    /**
     * 更新项目信息
     *
     * @param string $project_id 项目ID
     * @param string $project_name 项目名称
     * @param string $project_desc 项目描述
     * @param array $member_ids 项目成员ID
     * @return mixed
     */
    public function putProjects(string $project_id, string $project_name, string $project_desc, array $member_ids);


    /**
     * 删除项目
     *
     * @param string $project_id 项目ID
     * @return mixed
     */
    public function deleteProjects(string $project_id);


    /**
     * 获取项目成员
     *
     * @param string $project_id 项目ID
     * @return mixed
     */
    public function getProjectMembers(string $project_id);


    /**
     * 获取项目中所有任务清单 项目ID
     *
     * @param string $project_id
     * @return mixed
     */
    public function getProjectTodolists(string $project_id);


    /**
     * 创建任务清单
     *
     * @param string $project_id 项目ID
     * @param string $task_name 任务名称
     * @param string $task_desc 任务描述
     * @return mixed
     */
    public function createProjectTodolists(string $project_id, string $task_name, string $task_desc);


    /**
     * 获取任务清单信息
     *
     * @param string $id 任务清单ID
     * @return mixed
     */
    public function getTodolistInfo(string $id);


    /**
     * 更新任务列表
     *
     * @param string $id 任务ID
     * @param string $name 任务名称
     * @param string $desc 任务描述
     * @return mixed
     */
    public function putTodolists(string $id, string $name, string $desc);


    /**
     * 删除任务清单
     *
     * @param string $id 任务ID
     * @return mixed
     */
    public function deleteTodolists(string $id);


    /**
     * 获取清单任务
     *
     * @param string $todolist_id 清单ID
     * @param int $page page 从 1 开始计数
     * @param bool|false $completed_todo 是否包含已完成任务，默认：false
     * @return mixed
     */
    public function getTodos(string $todolist_id, int $page = 1, bool $completed_todo = false);


    /**
     * 创建任务
     *
     * @param string $todolist_id 清单ID
     * @param string $content 任务内容
     * @param string $desc 任务描述
     * @return mixed
     */
    public function createTodos(string $todolist_id, string $content, string $desc);


    /**
     * 获取任务信息
     *
     * @param string $todo_id 清单ID
     * @return mixed
     */
    public function getTodoInfo(string $todo_id);


    /**
     * 更新任务信息
     *
     * @param string $todo_id 清单ID
     * @param string $content 任务内容
     * @param string $desc 任务描述
     * @return mixed
     */
    public function putTodos(string $todo_id, string $content, string $desc);


    /**
     * 删除任务
     *
     * @param string $todo_id 清单ID
     * @return mixed
     */
    public function deleteTodos(string $todo_id);


    /**
     * 完成任务
     *
     * @param string $todo_id 清单ID
     * @return mixed
     */
    public function todosCompletion(string $todo_id);


    /**
     * 打开任务
     *
     * @param string $todo_id 清单ID
     * @return mixed
     */
    public function reopenCompletions(string $todo_id);


    /**
     * 发布评论
     *
     * @param string $id 清单ID
     * @param string $content 评论内容
     * @return mixed
     */
    public function createComments(string $id, string $content);


    /**
     * 指派任务负责人
     *
     * @param string $todo_id 清单ID
     * @param string $member_id 负责人ID
     * @return mixed
     */
    public function assignment(string $todo_id, string $member_id);


    /**
     * 移除任务负责人
     *
     * @param string $todo_id 清单ID
     * @return mixed
     */
    public function deleteAssignment(string $todo_id);


    /**
     * 更新任务到期日
     *
     * @param string $todo_id 清单ID
     * @param string $due_at 到期日
     * @return mixed
     */
    public function putDue(string $todo_id, string $due_at);


    /**
     * 更新任务描述
     *
     * @param string $todo_id
     * @param string $desc
     * @return mixed
     */
    public function putDesc(string $todo_id, string $desc);


}