<?php

declare(strict_types = 1);

/**
 * Author: 狂奔的螞蟻 <www.firstphp.com>
 * Date: 2019/12/17
 * Time: 1:12 PM
 */

namespace Firstphp\Tower;

use Firstphp\Tower\Bridge\Http;
use Hyperf\Utils\ApplicationContext;
use Psr\Container\ContainerInterface;
use Hyperf\Guzzle\ClientFactory;

class TowerClient implements TowerInterface
{

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $client_id;

    /**
     * @var string
     */
    protected $client_sercet;

    /**
     * @var string
     */
    protected $code;

    /**
     * @var object
     */
    protected $http;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var \Hyperf\Guzzle\ClientFactory
     */
    private $client;


    public function __construct(array $config = [], ContainerInterface $container, ClientFactory $clientFactory)
    {
        $config = $config ? $config : config('tower');
        if ($config) {
            $this->url = $config['url'];
            $this->client_id = $config['client_id'];
            $this->client_sercet = $config['client_sercet'];
            $this->code = $config['auth_code'];
        }
        $redis = ApplicationContext::getContainer()->get(\Redis::class);
        $tokenInfo = $redis->get("TOWER_TOKEN_INFO");
        $tokenInfo = json_decode($tokenInfo, true);
        $config['tokenInfo'] = $tokenInfo;
        $this->http = $container->make(Http::class, compact('config'));
        $this->client = $clientFactory;
    }


    /**
     * @return mixed
     */
    public function getAccessToken()
    {
        $redis = ApplicationContext::getContainer()->get(\Redis::class);
        $tokenInfo = $redis->get("TOWER_TOKEN_INFO");
        if ($tokenInfo) {
            $tokenInfo = json_decode($tokenInfo, true);
            $expiresAt = $tokenInfo['expires_in'] - 300 + $tokenInfo['created_at'];
            if (time() >= $expiresAt) {
//                $redis->del("TOWER_TOKEN_INFO");
                $options = [
                    'base_uri' => $this->url,
                    'timeout' => 200,
                    'verify' => false,
                    'headers' => [
                        'Authorization' => "Bearer {$tokenInfo['access_token']}",
                        'Content-Type' => "application/json;charset=utf-8",
                    ]
                ];

                $client = $this->client->create($options);

                $refreshToken = $client->post("oauth/token", [
                    'json' => [
                        'client_id' => $this->client_id,
                        'client_secret' => $this->client_sercet,
                        'grant_type' => 'refresh_token',
                        'redirect_uri' => 'urn:ietf:wg:oauth:2.0:oob',
                        'refresh_token' => $tokenInfo['refresh_token']
                    ]
                ]);
                $response = json_decode($refreshToken->getBody()->getContents(), true);
                if (isset($response['access_token']) && ($response['access_token'])) {
                    $redis->set("TOWER_TOKEN_INFO", json_encode($response));
                }
                return $response;
            }
            return $tokenInfo;
        } else {
            $options = [
                'base_uri' => $this->url,
                'timeout' => 200,
                'verify' => false,
            ];

            $client = $this->client->create($options);
            $response = $client->post("oauth/token", [
                'json' => [
                    'client_id' => $this->client_id,
                    'client_secret' => $this->client_sercet,
                    'code' => $this->code,
                    'grant_type' => 'authorization_code',
                    'redirect_uri' => 'urn:ietf:wg:oauth:2.0:oob',
                ]
            ]);
            $response = json_decode($response->getBody()->getContents(), true);

            if (isset($response['access_token'])) {
                $redis->set("TOWER_TOKEN_INFO", json_encode($response));
            }
            return $response;
        }
    }


    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->http->get("api/v1/user", [
            'query' => []
        ]);
    }


    /**
     * @return mixed
     */
    public function getTeams()
    {
        return $this->http->get("api/v1/teams", [
            'query' => []
        ]);
    }


    /**
     * 创建团队
     * @param string $teamname 团队名称
     * @return mixed
     */
    public function createTeams(string $teamname)
    {
        return $this->http->post("api/v1/teams", [
            'json' => [
                "team" => [
                    "name" => $teamname
                ]
            ]
        ]);
    }


    /**
     * 更改团队名称
     * @param int $id 团队ID
     * @param string $teamname 团队名称
     * @return mixed
     */
    public function putTeams(int $id, string $teamname) {
        return $this->http->patch("api/v1/teams/{$id}", [
            'json' => [
                "team" => [
                    "name" => $teamname
                ]
            ]
        ]);
    }


    /**
     * @param string $team_id
     * @param int $page
     * @param int $size
     * @return mixed
     */
    public function getNotifications(string $team_id, int $page = 1, int $size)
    {
        return $this->http->get("api/v1/teams/{$team_id}/notifications", [
            'query' => [
                "page" => [
                    "number" => $page,
                    "size" => $size
                ]
            ]
        ]);
    }


    /**
     * @param string $team_id
     * @param int $last_item_id
     * @param int $limit
     * @param string $by_member
     * @param string $by_project
     * @return mixed
     */
    public function getEvents(string $team_id, int $last_item_id, int $limit, string $by_member, string $by_project)
    {
        return $this->http->get("api/v1/teams/{$team_id}/notifications", [
            'query' => [
                "last_item_id" => $last_item_id,
                "limit" => $limit,
                "by_member" => $by_member,
                "by_project" => $by_project,
            ]
        ]);
    }


    /**
     * @param string $team_id
     * @param string $filename
     * @param int $byte_size
     * @param string $md5
     * @return mixed
     */
    public function getDirectUploads(string $team_id, string $filename, int $byte_size, string $md5)
    {
        return $this->http->post("api/v1/teams/{$team_id}/direct_uploads", [
            'json' => [
                "filename" => $filename,
                "byte_size" => $byte_size,
                "md5" => $md5,
            ]
        ]);
    }


    /**
     * @param string $team_id
     * @param int $page
     * @return mixed
     */
    public function getMembers(string $team_id, int $page = 1)
    {
        return $this->http->get("api/v1/teams/{$team_id}/members", [
            'query' => [
                "page" => [
                    "number" => $page
                ]
            ]
        ]);
    }


    /**
     * @param string $team_id
     * @return mixed
     */
    public function getMember(string $team_id)
    {
        return $this->http->get("api/v1/teams/{$team_id}/member", [
            'query' => []
        ]);
    }


    /**
     * @param string $member_id
     * @return mixed
     */
    public function getMemberInfo(string $member_id)
    {
        return $this->http->get("api/v1/members/{$member_id}", [
            'query' => []
        ]);
    }


    /**
     * @param string $member_id
     * @return mixed
     */
    public function getAssignedUncompletedTodos(string $member_id)
    {
        return $this->http->get("api/v1/members/{$member_id}/assigned_uncompleted_todos", [
            'query' => []
        ]);
    }


    /**
     * @param string $member_id
     * @param int $page
     * @return mixed
     */
    public function getAssignedCompletedTodos(string $member_id, int $page = 1)
    {
        return $this->http->get("api/v1/members/{$member_id}/assigned_completed_todos", [
            'query' => [
                "page" => [
                    "number" => $page
                ]
            ]
        ]);
    }


    /**
     * @param string $member_id
     * @param int $page
     * @return mixed
     */
    public function getCreatedCompletedTodos(string $member_id, int $page = 1)
    {
        return $this->http->get("api/v1/members/{$member_id}/created_completed_todos", [
            'query' => [
                "page" => [
                    "number" => $page
                ]
            ]
        ]);
    }


    /**
     * @param string $team_id
     * @return mixed
     */
    public function getProjects(string $team_id)
    {
        return $this->http->get("api/v1/teams/{$team_id}/projects", [
            'query' => []
        ]);
    }


    /**
     * @param string $team_id
     * @param string $project_name
     * @return mixed
     */
    public function createProjects(string $team_id, string $project_name)
    {
        return $this->http->post("api/v1/teams/{$team_id}/projects", [
            'json' => [
                "project" => [
                    "name" => $project_name,
                    "member_ids" => [],
                    "color_id" => 0,
                    "icon_id" => 0
                ]
            ]
        ]);
    }


    /**
     * @param string $project_id
     * @return mixed
     */
    public function getProjectsInfo(string $project_id)
    {
        return $this->http->get("api/v1/projects/{$project_id}", [
            'query' => []
        ]);
    }


    /**
     * @param string $project_id
     * @param string $project_name
     * @param string $project_desc
     * @param array $member_ids
     * @return mixed
     */
    public function putProjects(string $project_id, string $project_name, string $project_desc, array $member_ids)
    {
        return $this->http->patch("api/v1/projects/{$project_id}", [
            'json' => [
                "project" => [
                    "name" => $project_name,
                    "desc" => $project_desc,
                    "member_ids" => $member_ids
                ]
            ]
        ]);
    }


    /**
     * @param string $project_id
     * @return mixed
     */
    public function deleteProjects(string $project_id)
    {
        return $this->http->delete("api/v1/projects/{$project_id}", [
            'json' => []
        ]);
    }


    /**
     * @param string $project_id
     * @return mixed
     */
    public function getProjectMembers(string $project_id)
    {
        return $this->http->get("api/v1/projects/{$project_id}/members", [
            'query' => []
        ]);
    }


    /**
     * @param string $project_id
     * @return mixed
     */
    public function getProjectTodolists(string $project_id)
    {
        return $this->http->get("api/v1/projects/{$project_id}/todolists", [
            'query' => []
        ]);
    }


    /**
     * @param string $project_id
     * @param string $task_name
     * @param string $task_desc
     * @return mixed
     */
    public function createProjectTodolists(string $project_id, string $task_name, string $task_desc)
    {
        return $this->http->post("api/v1/projects/{$project_id}/todolists", [
            'json' => [
                "todolist" => [
                    "name" => $task_name,
                    "desc" => $task_desc,
                ]
            ]
        ]);
    }


    /**
     * @param string $id
     * @return mixed
     */
    public function getTodolistInfo(string $id)
    {
        return $this->http->get("api/v1/todolists/{$id}", [
            'query' => []
        ]);
    }


    /**
     * @param string $id
     * @param string $name
     * @param string $desc
     * @return mixed
     */
    public function putTodolists(string $id, string $name, string $desc)
    {
        return $this->http->put("api/v1/todolists/{$id}", [
            'query' => [
                "todolist" => [
                    "name" => $name,
                    "desc" => $desc
                ]
            ]
        ]);
    }


    /**
     * @param string $id
     * @return mixed
     */
    public function deleteTodolists(string $id)
    {
        return $this->http->delete("api/v1/todolists/{$id}", [
            'query' => []
        ]);
    }


    /**
     * @param string $todolist_id
     * @param int $page
     * @param bool|false $completed_todo
     * @return mixed
     */
    public function getTodos(string $todolist_id, int $page = 1, bool $completed_todo = false)
    {
        return $this->http->get("api/v1/todolists/{$todolist_id}/todos", [
            'query' => [
                "page" => [
                    "number" => $page
                ],
                "completed_todo" => $completed_todo
            ]
        ]);
    }


    /**
     * @param string $todolist_id
     * @param string $content
     * @param string $desc
     * @return mixed
     */
    public function createTodos(string $todolist_id, string $content, string $desc)
    {
        return $this->http->post("api/v1/todolists/{$todolist_id}/todos", [
            'json' => [
                "todo" => [
                    "content" => $content,
                    "desc" => $desc,
                    "assignee_id" => "",
                    "due_at" => ""
                ]
            ]
        ]);
    }


    /**
     * @param string $todo_id
     * @return mixed
     */
    public function getTodoInfo(string $todo_id)
    {
        return $this->http->get("api/v1/todos/{$todo_id}", [
            'query' => []
        ]);
    }


    /**
     * @param string $todo_id
     * @param string $content
     * @param string $desc
     * @return mixed
     */
    public function putTodos(string $todo_id, string $content, string $desc)
    {
        return $this->http->put("api/v1/todos/{$todo_id}", [
            'query' => [
                "todo" => [
                    "content" => $content,
                    "desc" => $desc
                ]
            ]
        ]);
    }


    /**
     * @param string $todo_id
     * @return mixed
     */
    public function deleteTodos(string $todo_id)
    {
        return $this->http->delete("api/v1/todos/{$todo_id}", [
            'query' => []
        ]);
    }


    /**
     * @param string $todo_id
     * @return mixed
     */
    public function todosCompletion(string $todo_id)
    {
        return $this->http->post("api/v1/todos/{$todo_id}/completion", [
            'json' => []
        ]);
    }


    /**
     * @param string $todo_id
     * @return mixed
     */
    public function reopenCompletions(string $todo_id)
    {
        return $this->http->delete("api/v1/todos/{$todo_id}/completion", [
            'query' => []
        ]);
    }


    /**
     * @param string $id
     * @param string $content
     * @return mixed
     */
    public function createComments(string $id, string $content)
    {
        return $this->http->post("api/v1/todos/{$id}/comments", [
            'json' => [
                "comment" => [
                    "content" => $content
                ]
            ]
        ]);
    }


    /**
     * @param string $todo_id
     * @param string $member_id
     * @return mixed
     */
    public function assignment(string $todo_id, string $member_id)
    {
        return $this->http->patch("api/v1/todos/{$todo_id}/assignment", [
            'json' => [
                "todos_assignment" => [
                    "assignee_id" => $member_id
                ]
            ]
        ]);
    }


    /**
     * @param string $todo_id
     * @return mixed
     */
    public function deleteAssignment(string $todo_id)
    {
        return $this->http->delete("api/v1/todos/{$todo_id}/assignment", [
            'json' => []
        ]);
    }


    /**
     * @param string $todo_id
     * @param string $due_at
     */
    public function putDue(string $todo_id, string $due_at)
    {
        return $this->http->put("api/v1/todos/{$todo_id}/due", [
            'json' => [
                "todos_due" => [
                    "due_at" => $due_at
                ]
            ]
        ]);
    }


    /**
     * @param string $todo_id
     * @param string $desc
     * @return mixed
     */
    public function putDesc(string $todo_id, string $desc)
    {
        return $this->http->patch("api/v1/todos/{$todo_id}/desc", [
            'json' => [
                "todos_desc" => [
                    "desc" => $desc
                ]
            ]
        ]);
    }

}