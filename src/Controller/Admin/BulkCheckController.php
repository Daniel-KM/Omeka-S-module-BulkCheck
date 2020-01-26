<?php
namespace BulkCheck\Controller\Admin;

use Log\Stdlib\PsrMessage;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class BulkCheckController extends AbstractActionController
{
    public function indexAction()
    {
        $form = $this->getForm(\BulkCheck\Form\BulkCheckForm::class);
        $view = new ViewModel;
        $view
            ->setVariable('form', $form);

        $request = $this->getRequest();
        if (!$request->isPost()) {
            return $view;
        }

        $params = $request->getPost();

        $form->init();
        $form->setData($params);
        if (!$form->isValid()) {
            $this->messenger()->addErrors($form->getMessages());
            return $view;
        }

        if (empty($params['process'])) {
            $this->messenger()->addWarning('No process submitted.'); // @translate
            return $view;
        }

        $params = $form->getData();
        unset($params['csrf']);

        $dispatcher = $this->jobDispatcher();

        switch ($params['process']) {
            case 'files_excess':
            case 'files_excess_move':
                $job = $dispatcher->dispatch(\BulkCheck\Job\FileExcess::class, $params);
                break;
            case 'files_missing':
                $job = $dispatcher->dispatch(\BulkCheck\Job\FileMissing::class, $params);
                break;
            case 'dirs_excess':
                $job = $dispatcher->dispatch(\BulkCheck\Job\DirExcess::class, $params);
                break;
            case 'filesize_check':
            case 'filesize_fix':
                $job = $dispatcher->dispatch(\BulkCheck\Job\FileSize::class, $params);
                break;
            case 'filehash_check':
            case 'filehash_fix':
                $job = $dispatcher->dispatch(\BulkCheck\Job\FileHash::class, $params);
                break;
            case 'db_job_check':
            case 'db_job_clean':
            case 'db_job_clean_all':
                $job = $dispatcher->dispatch(\BulkCheck\Job\DbJob::class, $params);
                break;
            case 'db_session_check':
            case 'db_session_clean':
                $job = $dispatcher->dispatch(\BulkCheck\Job\DbSession::class, $params);
                break;
            default:
                $this->messenger()->addError('Unknown process {process}', ['process' => $params['process']]); // @translate
                return $view;
        }

        $urlHelper = $this->url();
        $message = new PsrMessage(
            'Checking database and files in background (job {link_open_job}#{job_id}{link_close}, {link_open_log}logs{link_close}).', // @translate
            [
                'link_open_job' => sprintf(
                    '<a href="%s">',
                    htmlspecialchars($urlHelper->fromRoute('admin/id', ['controller' => 'job', 'id' => $job->getId()]))
                ),
                'job_id' => $job->getId(),
                'link_close' => '</a>',
                'link_open_log' => sprintf(
                    '<a href="%s">',
                    htmlspecialchars($urlHelper->fromRoute('admin/log/default', [], ['query' => ['job_id' => $job->getId()]]))
                ),
            ]
        );
        $message->setEscapeHtml(false);
        $this->messenger()->addSuccess($message);

        // Reset the form after a submission.
        $form = $this->getForm(\BulkCheck\Form\BulkCheckForm::class);
        return $view
            ->setVariable('form', $form);
    }
}
