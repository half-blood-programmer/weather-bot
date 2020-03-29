<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Http\ServerRequest;
use Cake\I18n\Time;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Users Model
 *
 * @method \App\Model\Entity\User newEmptyEntity()
 * @method \App\Model\Entity\User newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\User[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\User get($primaryKey, $options = [])
 * @method \App\Model\Entity\User findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\User patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\User[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\User|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\User saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class UsersTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('users');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');
    }

    /**
     * Default validation rules.
     *
     * @param Validator $validator Validator instance.
     * @return Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->boolean('available')
            ->notEmptyString('available');

        $validator
            ->scalar('first_name')
            ->requirePresence('first_name', 'create')
            ->notEmptyString('first_name');

        $validator
            ->scalar('username')
            ->requirePresence('username', 'create')
            ->allowEmptyString('username');

        $validator
            ->scalar('language_code')
            ->maxLength('language_code', 2)
            ->requirePresence('language_code', 'create')
            ->notEmptyString('language_code');

        $validator
            ->boolean('is_bot')
            ->requirePresence('is_bot', 'create')
            ->notEmptyString('is_bot');

        $validator
            ->integer('user_id')
            ->requirePresence('user_id', 'create')
            ->notEmptyString('user_id');

        $validator
            ->integer('chat_id')
            ->requirePresence('chat_id', 'create')
            ->notEmptyString('chat_id');

        $validator
            ->integer('city_id')
            ->allowEmptyString('city_id');

        $validator
            ->integer('message_id')
            ->allowEmptyString('message_id');

        $validator
            ->integer('last_updated')
            ->allowEmptyString('last_updated');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param RulesChecker $rules The rules object to be modified.
     * @return RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['user_id']));

        return $rules;
    }

    /**
     * @param $request
     * @return \App\Model\Entity\User|array|\Cake\Datasource\EntityInterface|null
     */
    public function getOrCreateUser(ServerRequest $request)
    {
        $user = $this->find()
            ->where(['user_id' => $request->getData('message.from.id')])
            ->first();

        if (!$user) {
            $user = $this->newEntity([
                'created' => Time::now()->timestamp,
                'available' => true,
                'first_name' => $request->getData('message.from.first_name'),
                'username' => $request->getData('message.from.username'),
                'language_code' => $request->getData('message.from.language_code'),
                'is_bot' => $request->getData('message.from.is_bot'),
                'user_id' => $request->getData('message.from.id'),
                'chat_id' => $request->getData('message.chat.id'),
            ]);
            $this->saveOrFail($user);
        }

        return $user;
    }
}
