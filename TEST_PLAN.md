# 测试计划 - symfony-lock-command-bundle

## 单元测试

| 模块 | 测试内容 | 状态 |
|------|---------|------|
| LockableCommand | 测试 getLockKey 方法的格式和一致性 | ✅ 已完成 |
| LockCommandEventSubscriber | 测试订阅事件及其优先级 | ✅ 已完成 |
| LockCommandExtension | 测试服务加载 | ✅ 已完成 |
| LockCommandBundle | 测试包实例 | ✅ 已完成 |

## 测试覆盖范围

当前的测试主要涵盖了以下功能点：

1. 锁键生成的格式和一致性
2. 事件订阅器的事件订阅和优先级
3. 依赖注入扩展加载服务
4. Bundle 实例化

## 待完善内容

1. 事件订阅器的功能性测试 - 目前只测试了订阅的事件和优先级，未来可以添加对以下功能的测试：
   - 对非 LockableCommand 的处理
   - 对没有锁键的命令的处理
   - 成功获取锁的情况
   - 获取锁失败的情况
   - 释放锁的功能
   - 异常处理

2. 集成测试 - 添加针对实际命令锁定功能的集成测试：
   - 测试并发命令的锁定机制
   - 测试锁超时和自动释放

## 测试执行

执行测试命令：

```bash
./vendor/bin/phpunit packages/symfony-lock-command-bundle/tests
``` 