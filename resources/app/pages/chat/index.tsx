import { MessagesService } from '@/apis/services/messages.service';
import receiveMessages from '@/assets/mp3/receive-message.mp3';
import sendMessageSound from '@/assets/mp3/send-message.mp3';
import { IMessages } from '@/models/messages';
import { IUser } from '@/models/user';
import { useAppDispatch, useAppSelector } from '@/redux/hook';
import { chatActions } from '@/redux/slice';
import { StorageFunc } from '@/utilities/local-storage/storage-func';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import { useEffect, useRef, useState } from 'react';
import { Card, Col, Row, Tab } from 'react-bootstrap';
import { useNavigate, useParams } from 'react-router-dom';
import { ChatBox } from './components/chat-box';
import { ChatForm } from './components/chat-form';
import { HeaderChat } from './components/header-chat';
import { PopUpDeleteChat } from './components/pop-up-delete-chat';
import { SideBar, queryKeyListChat } from './components/side-bar';

const audioSend = new Audio(sendMessageSound);
const audioReceive = new Audio(receiveMessages);

export const ChatPage = () => {
  // hook
  const { id: chat_id } = useParams<string>();

  const navigate = useNavigate();

  const dispatch = useAppDispatch();

  const { listPrivateChannel } = useAppSelector(state => state.chat);

  const { accessToken } = useAppSelector(state => state.auth);

  const queryClient = useQueryClient();

  //state
  const [showModal, setShowModal] = useState(false);

  const localUserId = StorageFunc.getUserId();

  const socketID = window.Echo.socketId();

  // func

  const sendMessageMutation = useMutation((messageText: string) => {
    return MessagesService.sendMessages(Number(chat_id), { content: messageText }, socketID);
  });
  const handleSendMessage = (message: string) => {
    sendMessageMutation.mutate(message, {
      onSuccess: ({ data }) => {
        audioSend.play();
        dispatch(chatActions.addMessageToListMessage(data.data as IMessages));
      },
    });
  };

  const handleConfirmDeleteChat = () => {
    dispatch(chatActions.removeChannel(Number(chat_id)));
    // setShowModal(true);
  };

  //xoá đoạn chat
  const deleteMessageMutation = useMutation(
    (channelId: number) => {
      return MessagesService.deleteChatChannel(channelId);
    },
    {
      onSuccess: () => {
        queryClient.invalidateQueries(queryKeyListChat);
        navigate('/chat');
      },
    },
  );
  const handleConfirmDelete = () => {
    // deleteMessageMutation.mutate(Number(chat_id));
    console.log('xoa');

    setShowModal(false);
  };

  useEffect(() => {
    if (!accessToken) return;

    window.Echo.connector.options.auth.headers['Authorization'] = `Bearer ${accessToken}`;

    const handlePrivateMessage = (event: any) => {
      try {
        const { sender_id, action = 'send' } = event.message;

        console.log('🤪 action', action);

        if (action === 'delete') {
          return dispatch(chatActions.removeMessageFromListMessage(event.message.id));
        }

        // Nếu người gửi chưa có trong danh sách chat thì cập nhật lại danh sách user chat
        const isNewSender = listPrivateChannel?.findIndex((item: IUser) => item.id === sender_id) === -1;

        console.log('isNewSender', isNewSender);

        if (isNewSender) {
          queryClient.invalidateQueries(queryKeyListChat);
        }

        audioReceive.play();

        dispatch(chatActions.addMessageToListMessage(event.message));
      } catch (error) {
        console.log('handlePrivateMessage', error);
      }
    };

    window.Echo.private(`user.${localUserId}`).listen('.PrivateMessageSent', handlePrivateMessage);
    return () => {
      window.Echo.private(`user.${localUserId}`).stopListening('.PrivateMessageSent', handlePrivateMessage);
    };
  }, []);

  // render

  return (
    <>
      <>
        <PopUpDeleteChat showModal={showModal} onClose={() => setShowModal(false)} onDelete={handleConfirmDelete} />
      </>
      <div id="content-page" className="content-page p-0">
        <Row>
          <Col sm="12">
            <Card className="mb-0">
              <Card.Body className="chat-page p-0">
                <div className="chat-data-block">
                  <Row>
                    <Col lg={3} className="chat-data-left scroller" style={{ paddingRight: '2px' }}>
                      <SideBar />
                    </Col>
                    <Col lg={9} className="chat-data p-0 chat-data-right border-start">
                      {chat_id ? (
                        <Tab.Content>
                          <div style={{ position: 'relative', minHeight: '100%' }}>
                            <HeaderChat onClickRemoveChat={handleConfirmDeleteChat} />

                            <ChatBox />

                            <ChatForm onSend={handleSendMessage} />
                          </div>
                        </Tab.Content>
                      ) : (
                        <div className="d-flex align-items-center justify-content-center" style={{ minHeight: '100%' }}>
                          <h1>Chat với bạn bè nào 💬 ...</h1>
                        </div>
                      )}
                    </Col>
                  </Row>
                </div>
              </Card.Body>
            </Card>
          </Col>
        </Row>
      </div>
    </>
  );
};
