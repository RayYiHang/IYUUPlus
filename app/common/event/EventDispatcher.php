<?php
declare(strict_types=1);

namespace app\common\event;

use Throwable;

/**
 * 事件调度器
 * Class EventDispatcher
 */
class EventDispatcher
{
    /**
     * 所有事件监听器
     * @var EventListenerInterface
     */
    protected $eventListeners = [];

    /**
     * 构造函数
     * @descr 初始化所有事件监听器
     * @param EventListenerInterface[] $listeners
     */
    public function __construct(array $listeners)
    {
        $eventListeners = [];
        foreach ($listeners as $listener) {
            foreach ($listener->events() as $event) {
                $eventListeners[$event][] = $listener;
            }
        }
        $this->eventListeners = $eventListeners;
    }

    /**
     * 派发当前事件到所有监听器的处理方法process
     * @param object $event 当前事件对象
     * @return object
     */
    public function dispatch(object $event): object
    {
        foreach ($this->getListenersForEvent($event) as $callback) {
            try {
                call_user_func($callback, $event);
            } catch (Throwable $ex) {
                //Logger::error(__METHOD__ . '调度异常：' . $ex->getMessage());
            }
            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                break;
            }
        }
        return $event;
    }

    /**
     * 检出当前事件的所有监听器
     * @param object $event 当前事件类
     * @return iterable 可迭代对象
     */
    public function getListenersForEvent(object $event): iterable
    {
        $class = get_class($event);
        $listeners = $this->eventListeners[$class] ?? [];
        $iterable = [];
        foreach ($listeners as $listener) {
            $iterable[] = [$listener, 'process'];
        }
        return $iterable;
    }
}
