<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\TaskRepository;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class ApiController
 * @package App\Controller
 * @Rest\Route("/api")
 */
class ApiController extends AbstractFOSRestController
{
    /** @var ValidatorInterface */
    private $validator;
    /** @var TaskRepository */
    private $taskRepository;

    /** @var Logger */
    private $logger;

    /**
     * ApiController constructor.
     * @param ValidatorInterface $validator
     * @param TaskRepository $taskRepository
     */
    public function __construct(
        ValidatorInterface $validator,
        TaskRepository $taskRepository
    )
    {
        $this->validator = $validator;
        $this->taskRepository = $taskRepository;

        $this->logger = new Logger('api');
        $logFileName = __DIR__ . '/../../var/log/api_v1.log';
        $this->logger->pushHandler(new RotatingFileHandler($logFileName, 5));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     *
     * @Rest\Post("/v1/task")
     *
     * @SWG\Tag(name="Create Task")
     * @SWG\Response(response="200",description="Task created")
     * @SWG\Response(response="400",description="Not enought data")
     * @SWG\Response(response="500",description="Server error")
     * @SWG\Parameter(name="text",in="formData",type="string",required=true)
     */
    public function createTask(
        Request $request
    ): JsonResponse
    {
        $this->logger->info('create task - ' . json_encode($request->get('text')));

        $task = new Task();
        $task->setText($request->get('text'));
        $result = $this->validateAndSaveTask($task);

        return new JsonResponse($result['answer'], $result['status']);
    }

    /**
     * @param int $id
     * @return JsonResponse
     *
     * @Rest\Patch("/v1/task/{id}")
     *
     * @SWG\Tag(name="Mark As Performed Task")
     * @SWG\Response(response="200",description="Task mark as performed")
     * @SWG\Response(response="400",description="Task is not exists")
     * @SWG\Response(response="500",description="Server error")
     */
    public function markAsPerformedTask(
        int $id
    ): JsonResponse
    {
        //TODO: при большой нагрузке выполненные задачи можно вынести в отдельную таблицу
        $this->logger->info('mark as read task - ' . $id);

        $task = $this->taskRepository->find($id);
        if(null == $task){
            return new JsonResponse('Id not found', 400);
        }

        $task->setPerformed(true);
        $result = $this->validateAndSaveTask($task);

        return new JsonResponse($result['answer'], $result['status']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     *
     * @Rest\Get("/v1/task")
     *
     * @SWG\Tag(name="Get Unperformed Tasks")
     * @SWG\Response(response="200",description="Get unperformed tasks")
     */
    public function getUnperformedTasks(
        Request $request
    ): JsonResponse
    {
        $limit = $request->get('limit', 100);
        if (!is_int($limit)) {
            return new JsonResponse('limit should be integer', 400);
        }

        $result = $this->taskRepository->getUnperformedTasks($limit);
        return new JsonResponse($result);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     *
     * @Rest\Get("/v1/task/performed")
     *
     * @SWG\Tag(name="Get Performed Tasks")
     * @SWG\Response(response="200",description="Get performed tasks")
     */
    public function getPerformedTasks(
        Request $request
    ): JsonResponse
    {
        //TODO: при большой нагрузке выполненные задачи можно вынести в отдельную таблицу

        $limit = $request->get('limit', 100);
        if (!is_int($limit)) {
            return new JsonResponse('limit should be integer', 400);
        }

        $result = $this->taskRepository->getPerformedTasks($limit);
        return new JsonResponse($result);
    }


    /**
     * @return JsonResponse
     *
     * @Rest\Get("/v1/task/existing-task-id")
     *
     * @SWG\Tag(name="Get existing task id - for postman tests")
     * @SWG\Response(response="200",description="Get id")
     */
    public function getExistingTaskId(): JsonResponse
    {
        $task = $this->taskRepository->findOneBy([]);

        return new JsonResponse($task->getId());
    }

    /**
     * @param int $id
     * @return JsonResponse
     *
     * @Rest\Delete("/v1/task/{id}")
     *
     * @SWG\Tag(name="Delete Task")
     * @SWG\Response(response="200",description="Task deleted")
     * @SWG\Response(response="400",description="Task is not exists")
     * @SWG\Response(response="500",description="Server error")
     */
    public function deleteTask(int $id): JsonResponse
    {
        $task = $this->taskRepository->find($id);
        if(null == $task){
            return new JsonResponse('Id not found', 400);
        }

        $this->logger->info('delete task - ' . $id);

        $result = $this->taskRepository->delete($task);
        if ($result !== 'ok') {
            $this->logger->critical('error delete - ' . $result);
            return new JsonResponse($result, 500);
        }

        return new JsonResponse($result);
    }


    private function validateAndSaveTask(Task $task): array
    {
        $errors = $this->validator->validate($task);
        if (count($errors) > 0) {
            return ['answer' => (string)$errors, 'status' => 400];
        }

        $result = $this->taskRepository->save($task);
        if ($result !== 'ok') {
            $this->logger->critical('error saving - ' . $result);
            return ['answer' => ($result), 'status' => 500];
        }

        return ['answer' => ($result), 'status' => 200];
    }

}
