import ActionButton from '@/components/ActionButton';
import { openConfirmModal } from '@/components/ConfirmModal';
import useForm from '@/hooks/useForm';
import ContainerBox from '@/layouts/ContainerBox';
import Layout from '@/layouts/MainLayout';
import { router, usePage } from '@inertiajs/react';
import {
  ActionIcon,
  Alert,
  Button,
  Code,
  CopyButton,
  Grid,
  Group,
  Select,
  Stack,
  Text,
  TextInput,
  Title,
  Tooltip,
} from '@mantine/core';
import { IconCheck, IconCopy, IconPlugConnected, IconTrash } from '@tabler/icons-react';

const MondayIntegrationPage = () => {
  const { project, board, integration, webhookUrl, dropdowns } = usePage().props;

  const [form, submit, updateValue] = useForm(
    'post',
    route('projects.boards.integrations.monday.store', [project.id, board.id]),
    {
      monday_board_id: integration?.monday_board_id ? String(integration.monday_board_id) : '',
      monday_user_id: integration?.monday_user_id ? String(integration.monday_user_id) : '',
      task_group_id: integration?.task_group_id ? String(integration.task_group_id) : null,
      assigned_user_id: integration?.assigned_user_id ? String(integration.assigned_user_id) : null,
    }
  );

  const openDeleteModal = () =>
    openConfirmModal({
      type: 'danger',
      title: 'Remove integration',
      content: 'Are you sure you want to remove the Monday.com integration for this board?',
      confirmLabel: 'Remove',
      confirmProps: { color: 'red' },
      onConfirm: () =>
        router.delete(
          route('projects.boards.integrations.monday.destroy', [project.id, board.id]),
          { preserveScroll: true }
        ),
    });

  return (
    <>
      <Grid
        justify='space-between'
        align='flex-end'
        gutter='xl'
        mb='lg'
      >
        <Grid.Col span='auto'>
          <Group gap='xs'>
            <IconPlugConnected size={24} />
            <Title order={1}>Monday.com Integration</Title>
          </Group>
          <Text
            c='dimmed'
            mt='xs'
          >
            {board.name} — {project.name}
          </Text>
        </Grid.Col>
      </Grid>

      <ContainerBox maw={600}>
        <form onSubmit={submit}>
          <Stack gap='md'>
            {webhookUrl && (
              <Alert
                title='Webhook URL'
                color='blue'
                variant='light'
              >
                <Text
                  size='sm'
                  mb='xs'
                >
                  Paste this URL into Monday.com → Board → Integrate → Webhooks → &quot;When a
                  person is assigned to an item&quot;.
                </Text>
                <Group gap='xs'>
                  <Code style={{ flex: 1, wordBreak: 'break-all' }}>{webhookUrl}</Code>
                  <CopyButton
                    value={webhookUrl}
                    timeout={2000}
                  >
                    {({ copied, copy }) => (
                      <Tooltip
                        label={copied ? 'Copied' : 'Copy URL'}
                        withArrow
                      >
                        <ActionIcon
                          color={copied ? 'teal' : 'blue'}
                          variant='light'
                          onClick={copy}
                        >
                          {copied ? <IconCheck size={16} /> : <IconCopy size={16} />}
                        </ActionIcon>
                      </Tooltip>
                    )}
                  </CopyButton>
                </Group>
              </Alert>
            )}

            <TextInput
              label='Monday.com Board ID'
              description='The numeric ID of your Monday.com board (found in the URL)'
              placeholder='e.g. 1234567890'
              value={form.data.monday_board_id}
              onChange={e => updateValue('monday_board_id', e.target.value)}
              error={form.errors.monday_board_id}
              required
            />

            <TextInput
              label='Monday.com User ID'
              description='Your numeric Monday.com user ID. Find it at monday.com/users/{id} or in your profile URL.'
              placeholder='e.g. 9876543'
              value={form.data.monday_user_id}
              onChange={e => updateValue('monday_user_id', e.target.value)}
              error={form.errors.monday_user_id}
              required
            />

            <Select
              label='Import into task group'
              description='New tasks from Monday.com will be placed in this group'
              placeholder='Select a task group'
              data={dropdowns.taskGroups}
              value={form.data.task_group_id}
              onChange={value => updateValue('task_group_id', value)}
              error={form.errors.task_group_id}
              required
            />

            <Select
              label='Assign tasks to'
              description='Tasks imported from Monday.com will be assigned to this user'
              placeholder='Select a user'
              data={dropdowns.users}
              value={form.data.assigned_user_id}
              onChange={value => updateValue('assigned_user_id', value)}
              error={form.errors.assigned_user_id}
              searchable
              required
            />

            <Group
              justify='space-between'
              mt='xs'
            >
              {integration && (
                <Button
                  variant='subtle'
                  color='red'
                  leftSection={<IconTrash size={14} />}
                  onClick={openDeleteModal}
                >
                  Remove integration
                </Button>
              )}
              <ActionButton
                ml='auto'
                type='submit'
                loading={form.processing}
              >
                {integration ? 'Save changes' : 'Enable integration'}
              </ActionButton>
            </Group>
          </Stack>
        </form>
      </ContainerBox>
    </>
  );
};

MondayIntegrationPage.layout = page => <Layout>{page}</Layout>;

export default MondayIntegrationPage;
