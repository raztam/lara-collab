import UpdateBoardModal from './UpdateBoardModal';
import { Link, router } from '@inertiajs/react';
import { ActionIcon, Badge, Card, Group, Text, Tooltip } from '@mantine/core';
import {
  IconChevronRight,
  IconLayoutKanban,
  IconListCheck,
  IconPencil,
  IconPlugConnected,
} from '@tabler/icons-react';
import { useState } from 'react';
import classes from './css/BoardCard.module.css';

const ACCENT_COLORS = [
  '#3b82f6', // blue
  '#8b5cf6', // violet
  '#06b6d4', // cyan
  '#10b981', // emerald
  '#f59e0b', // amber
  '#ef4444', // red
  '#ec4899', // pink
  '#6366f1', // indigo
];

function getAccentColor(name) {
  let hash = 0;
  for (let i = 0; i < name.length; i++) {
    hash = name.charCodeAt(i) + ((hash << 5) - hash);
  }
  return ACCENT_COLORS[Math.abs(hash) % ACCENT_COLORS.length];
}

export default function BoardCard({ board, project }) {
  const accentColor = getAccentColor(board.name);
  const groupCount = board.task_groups_count ?? 0;
  const taskCount = board.tasks_count ?? 0;
  const [hovered, setHovered] = useState(false);

  return (
    <div
      style={{ position: 'relative' }}
      onMouseEnter={() => setHovered(true)}
      onMouseLeave={() => setHovered(false)}
    >
      <Link
        href={route('projects.boards.show', [project.id, board.id])}
        className={classes.link}
      >
      <Card
        withBorder
        padding='xl'
        radius='md'
        w={300}
        className={classes.card}
        style={{ '--board-accent-color': accentColor, cursor: 'pointer' }}
      >
        <Group
          justify='space-between'
          mb={board.description ? 'xs' : 0}
          wrap='nowrap'
          align='flex-start'
        >
          <Text className={classes.title}>{board.name}</Text>
          {board.is_default && (
            <Badge
              variant='dot'
              size='sm'
              color={accentColor}
              style={{ flexShrink: 0, marginTop: 2 }}
            >
              Default
            </Badge>
          )}
        </Group>

        {board.description && (
          <Text
            fz='sm'
            c='dimmed'
            lineClamp={2}
          >
            {board.description}
          </Text>
        )}

        <div className={classes.statsRow}>
          <div className={classes.stat}>
            <div className={classes.statIcon}>
              <IconLayoutKanban size={14} />
            </div>
            <div className={classes.statText}>
              <span className={classes.statValue}>{groupCount}</span>
              <span className={classes.statLabel}>{groupCount === 1 ? 'Group' : 'Groups'}</span>
            </div>
          </div>
          <div className={classes.statDivider} />
          <div className={classes.stat}>
            <div className={classes.statIcon}>
              <IconListCheck size={14} />
            </div>
            <div className={classes.statText}>
              <span className={classes.statValue}>{taskCount}</span>
              <span className={classes.statLabel}>Open {taskCount === 1 ? 'Task' : 'Tasks'}</span>
            </div>
          </div>
          <IconChevronRight
            size={15}
            className={classes.arrow}
          />
        </div>
      </Card>
    </Link>

    {can('edit task group') && (
      <>
        <Tooltip
          label='Integrations'
          withArrow
        >
          <ActionIcon
            variant='subtle'
            size='sm'
            style={{
              position: 'absolute',
              top: 10,
              right: 32,
              opacity: hovered ? 1 : 0,
              transition: 'opacity 150ms ease',
            }}
            onClick={e => {
              e.preventDefault();
              router.visit(
                route('projects.boards.integrations.monday.show', [project.id, board.id])
              );
            }}
          >
            <IconPlugConnected size={13} />
          </ActionIcon>
        </Tooltip>
        <Tooltip
          label='Rename board'
          withArrow
        >
          <ActionIcon
            variant='subtle'
            size='sm'
            style={{
              position: 'absolute',
              top: 10,
              right: 10,
              opacity: hovered ? 1 : 0,
              transition: 'opacity 150ms ease',
            }}
            onClick={e => {
              e.preventDefault();
              UpdateBoardModal(board);
            }}
          >
            <IconPencil size={13} />
          </ActionIcon>
        </Tooltip>
      </>
    )}
  </div>
  );
}
